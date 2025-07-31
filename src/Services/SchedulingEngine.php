<?php
namespace App\Services;

use App\Models\{Student, Teacher, Room, Subject, Curriculum, TimeSlot, ClassModel, ConflictExemption};

class SchedulingEngine
{
    private array $exemptions = [];

    public function __construct()
    {
        $this->loadExemptions();
    }

    private function loadExemptions(): void
    {
        $exemptions = ConflictExemption::where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->get();

        foreach ($exemptions as $e) {
            $this->exemptions["{$e->type}_{$e->entity_id}"][$e->conflict_type] = $e->reason;
        }
    }

    public function isExempt(string $type, string $id, string $conflict): bool
    {
        return isset($this->exemptions["{$type}_{$id}"][$conflict]);
    }

    public function generateSchedule(array $filters = []): array
    {
        $term = $filters['term'] ?? null;

        $query = ClassModel::query();
        if ($term) $query->where('term', $term);
        $manualClasses = $query->where('is_override', true)->get()->toArray();

        // Build schedule from scratch
        $schedule = [];

        $subjects = Subject::all();
        $teachers = Teacher::all();
        $rooms = Room::all();
        $timeSlots = TimeSlot::where('is_active', true)->get();
        $curriculums = Curriculum::when($term, fn($q) => $q->where('term', $term))->get();
        $students = Student::all();

        $usedSlots = [];        // teacher/day/slot
        $roomSlots = [];        // room/day/slot
        $studentSlots = [];     // student/day/slot

        // Pre-load manual overrides
        foreach ($manualClasses as $cls) {
            $tid = $cls['teacher_id'];
            $rid = $cls['room_id'];
            $sid = $cls['subject_id'];
            $day = $cls['day'];
            $tsId = $cls['time_slot_id'];

            $usedSlots[$tid][$day][$tsId] = true;
            $roomSlots[$rid][$day][$tsId] = true;

            // Assign to all students in subject's curriculum
            $subject = $subjects->firstWhere('id', $sid);
            $curriculum = $curriculums->firstWhere('subjectIds', 'LIKE', "%\"$sid\"%");
            if ($curriculum) {
                foreach ($students as $student) {
                    if ($student->curriculumId === $curriculum->id) {
                        $studentSlots[$student->id][$day][$tsId] = true;
                    }
                }
            }

            $schedule[] = $cls;
        }

        foreach ($curriculums as $curriculum) {
            foreach ($curriculum->subjectIds as $subjectId) {
                $subject = $subjects->find($subjectId);
                if (!$subject) continue;

                $sessionsNeeded = max(1, (int)($subject->weeklyHours / $subject->units));

                $assigned = 0;
                $qualifiedTeachers = $teachers->whereIn('id', function($q) use ($subjectId) {
                    $q->from('teachers')->whereRaw("JSON_CONTAINS(qualified_subject_ids, '\"$subjectId\"')");
                });

                foreach ($qualifiedTeachers as $teacher) {
                    if ($assigned >= $sessionsNeeded) break;

                    foreach (['Mon','Tue','Wed','Thu','Fri'] as $day) {
                        if ($assigned >= $sessionsNeeded) break;

                        foreach ($timeSlots as $ts) {
                            $tsId = $ts->id;

                            // Skip if teacher is busy
                            if (isset($usedSlots[$teacher->id][$day][$tsId]) &&
                                !$this->isExempt('teacher', $teacher->id, 'schedule')) {
                                continue;
                            }

                            // Skip if room is busy
                            $freeRoom = null;
                            foreach ($rooms as $room) {
                                if (!isset($roomSlots[$room->id][$day][$tsId])) {
                                    $freeRoom = $room;
                                    break;
                                }
                            }
                            if (!$freeRoom && !$this->isExempt('room', 'any', 'capacity')) {
                                continue;
                            }

                            // Check student conflicts
                            $studentsInCurriculum = $students->where('curriculumId', $curriculum->id);
                            $canSchedule = true;
                            foreach ($studentsInCurriculum as $student) {
                                if (isset($studentSlots[$student->id][$day][$tsId]) &&
                                    !$this->isExempt('student', $student->id, 'schedule')) {
                                    $canSchedule = false;
                                    break;
                                }
                            }

                            if (!$canSchedule) continue;

                            // Assign
                            $roomId = $freeRoom ? $freeRoom->id : $rooms->first()->id;

                            $class = [
                                'class_id' => 'cls_' . uniqid(),
                                'subject_id' => $subjectId,
                                'teacher_id' => $teacher->id,
                                'room_id' => $roomId,
                                'time_slot_id' => $tsId,
                                'day' => $day,
                                'term' => $curriculum->term,
                                'is_override' => false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $schedule[] = $class;

                            $usedSlots[$teacher->id][$day][$tsId] = true;
                            $roomSlots[$roomId][$day][$tsId] = true;
                            foreach ($studentsInCurriculum as $student) {
                                $studentSlots[$student->id][$day][$tsId] = true;
                            }

                            $assigned++;
                            break 2;
                        }
                    }
                }
            }
        }

        return $schedule;
    }
}