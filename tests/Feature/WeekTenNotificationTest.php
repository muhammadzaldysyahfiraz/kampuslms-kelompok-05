<?php

namespace Tests\Feature;

use App\Events\AssignmentPublished;
use App\Events\GradeReleased;
use App\Listeners\SendAssignmentNotification;
use App\Listeners\SendGradeNotification;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeReleasedNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class WeekTenNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_published_assignment_event_queues_its_listener(): void
    {
        $lecturer = $this->createUser('dosen');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        Queue::fake();

        AssignmentPublished::dispatch($assignment);

        Queue::assertPushed(CallQueuedListener::class, fn (CallQueuedListener $job) =>
            $job->class === SendAssignmentNotification::class
        );
    }

    public function test_publishing_assignment_from_web_dispatches_event_once(): void
    {
        $lecturer = $this->createUser('dosen');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Event::fake([AssignmentPublished::class]);

        $this->actingAs($lecturer)
            ->post(route('dosen.courses.assignments.store', $course), [
                'title' => 'Tugas Integrasi',
                'instructions' => 'Kerjakan sesuai instruksi.',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'max_score' => 100,
                'allow_late' => true,
                'status' => 'published',
            ])
            ->assertRedirect();

        Event::assertDispatched(AssignmentPublished::class);
    }

    public function test_publishing_assignment_from_api_dispatches_event(): void
    {
        $lecturer = $this->createUser('dosen');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        Event::fake([AssignmentPublished::class]);

        $this->actingAs($lecturer, 'sanctum')
            ->postJson('/api/v1/assignments', [
                'course_id' => $course->id,
                'title' => 'Tugas API',
                'instructions' => 'Kerjakan sesuai instruksi.',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'max_score' => 100,
                'allow_late' => true,
                'status' => 'published',
            ])
            ->assertCreated();

        Event::assertDispatched(AssignmentPublished::class);
    }

    public function test_assignment_listener_notifies_only_enrolled_students(): void
    {
        Notification::fake();
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');
        $outsider = $this->createUser('mahasiswa');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);

        (new SendAssignmentNotification())->handle(new AssignmentPublished($assignment));

        Notification::assertSentTo($student, NewAssignmentNotification::class);
        Notification::assertNotSentTo($outsider, NewAssignmentNotification::class);

        $payload = (new NewAssignmentNotification($assignment->load('course')))->toDatabase($student);
        $this->assertArrayHasKey('assignment_id', $payload);
        $this->assertArrayNotHasKey('score', $payload);
        $this->assertArrayNotHasKey('feedback', $payload);
    }

    public function test_grade_event_queues_listener_and_payload_does_not_include_grade_details(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
        $grade = Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 88,
            'feedback' => 'Feedback privat',
        ]);
        Queue::fake();

        GradeReleased::dispatch($grade);

        Queue::assertPushed(CallQueuedListener::class, fn (CallQueuedListener $job) =>
            $job->class === SendGradeNotification::class
        );

        $payload = (new GradeReleasedNotification($grade))->toDatabase($student);
        $this->assertArrayHasKey('grade_id', $payload);
        $this->assertArrayNotHasKey('score', $payload);
        $this->assertArrayNotHasKey('feedback', $payload);
        $this->assertStringNotContainsString('88', json_encode($payload));
        $this->assertStringNotContainsString('Feedback privat', json_encode($payload));
    }

    public function test_saving_grade_from_web_dispatches_grade_event(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
        Event::fake([GradeReleased::class]);

        $this->actingAs($lecturer)
            ->post(route('submissions.grade.store', $submission), ['score' => 91])
            ->assertRedirect();

        Event::assertDispatched(GradeReleased::class, fn (GradeReleased $event) =>
            $event->grade->submission_id === $submission->id
        );
    }

    public function test_saving_grade_from_api_dispatches_grade_event(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
        Event::fake([GradeReleased::class]);

        $this->actingAs($lecturer, 'sanctum')
            ->putJson("/api/v1/submissions/{$submission->id}/grade", ['score' => 91])
            ->assertCreated();

        Event::assertDispatched(GradeReleased::class, fn (GradeReleased $event) =>
            $event->grade->submission_id === $submission->id
        );
    }

    public function test_student_cannot_mark_another_students_notification_as_read(): void
    {
        $owner = $this->createUser('mahasiswa');
        $otherStudent = $this->createUser('mahasiswa');
        $notification = new DatabaseNotification([
            'id' => (string) Str::uuid(),
            'type' => NewAssignmentNotification::class,
            'data' => ['title' => 'Tugas baru'],
        ]);
        $owner->notifications()->save($notification);

        $this->actingAs($otherStudent)
            ->patch(route('notifications.read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}