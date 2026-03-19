<?php

namespace Tests\Feature;

use App\Services\StudentService;
use Tests\TestCase;

class StudentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        StudentService::reset();
    }

    public function test_get_students_returns_array()
    {
        $response = $this->get('/api/students');
        $response->assertStatus(200);
        $response->assertJsonIsArray();
    }

    public function test_get_students_returns_initial_data()
    {
        $response = $this->get('/api/students');
        $response->assertStatus(200);
        $this->assertCount(5, $response->json());
    }

    public function test_get_student_by_valid_id()
    {
        $response = $this->get('/api/students/1');
        $response->assertStatus(200);
        $this->assertEquals(100000, $response->json('id'));
        $this->assertEquals('John', $response->json('firstName'));
    }

    public function test_get_student_not_found()
    {
        $response = $this->get('/api/students/999');
        $response->assertStatus(404);
    }

    public function test_get_student_invalid_id()
    {
        $response = $this->get('/api/students/abc');
        $response->assertStatus(400);
    }

    public function test_create_valid_student()
    {
        $data = [
            'firstName' => 'New',
            'lastName' => 'Student',
            'email' => 'new@example.com',
            'grade' => 14,
            'field' => 'informatique',
        ];
        $response = $this->postJson('/api/students', $data);
        $response->assertStatus(201);
        $this->assertArrayHasKey('id', $response->json());
        $this->assertEquals('new@example.com', $response->json('email'));
    }

    public function test_create_student_missing_field()
    {
        $data = [
            'firstName' => 'Fail',
        ];
        $response = $this->postJson('/api/students', $data);
        $response->assertStatus(400);
    }

    public function test_create_student_invalid_grade()
    {
        $data = [
            'firstName' => 'New',
            'lastName' => 'Student',
            'email' => 'new2@example.com',
            'grade' => 25,
            'field' => 'informatique',
        ];
        $response = $this->postJson('/api/students', $data);
        $response->assertStatus(400);
    }

    public function test_create_student_existing_email()
    {
        $data = [
            'firstName' => 'New',
            'lastName' => 'Student',
            'email' => 'john.doe@example.com',
            'grade' => 14,
            'field' => 'informatique',
        ];
        $response = $this->postJson('/api/students', $data);
        $response->assertStatus(409);
    }

    public function test_update_valid_student()
    {
        $data = [
            'firstName' => 'Jane Updated',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'grade' => 20,
            'field' => 'mathématiques',
        ];
        $response = $this->putJson('/api/students/2', $data);
        $response->assertStatus(200);
        $this->assertEquals('Jane Updated', $response->json('firstName'));
        $this->assertEquals(20, $response->json('grade'));
    }

    public function test_update_student_not_found()
    {
        $data = [
            'firstName' => 'Ghost',
            'lastName' => 'Ghost',
            'email' => 'ghost@example.com',
            'grade' => 10,
            'field' => 'informatique',
        ];
        $response = $this->putJson('/api/students/999', $data);
        $response->assertStatus(404);
    }

    public function test_delete_valid_student()
    {
        $response = $this->delete('/api/students/3');
        $response->assertStatus(200);

        $check = $this->get('/api/students/3');
        $check->assertStatus(404);
    }

    public function test_delete_student_not_found()
    {
        $response = $this->delete('/api/students/999');
        $response->assertStatus(404);
    }

    public function test_stats()
    {
        $response = $this->get('/api/students/stats');
        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals(5, $json['totalStudents']);
        $this->assertArrayHasKey('averageGrade', $json);
        $this->assertArrayHasKey('studentsByField', $json);
        $this->assertEquals(18, $json['bestStudent']);
    }

    public function test_search_results()
    {
        $response = $this->get('/api/students/search?q=john');
        $response->assertStatus(200);
        $json = $response->json();
        $this->assertCount(2, $json); // John Doe & Alice Johnson
    }
}
