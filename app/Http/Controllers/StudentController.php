<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index()
    {
        return response()->json(StudentService::all());
    }

    public function stats()
    {
        $all = StudentService::all();
        $total = count($all);
        $averageGrade = 0;
        $bestStudent = 0;
        $studentsByField = [];

        if ($total > 0) {
            $sum = 0;
            foreach ($all as $student) {
                $sum += $student['grade'];
                if ($student['grade'] > $bestStudent) {
                    $bestStudent = $student['grade'];
                }
                $field = $student['field'];
                $studentsByField[$field] = ($studentsByField[$field] ?? 0) + 1;
            }
            $averageGrade = round($sum / $total, 2);
        }

        return response()->json([
            'totalStudents' => $total,
            'averageGrade' => $averageGrade,
            'studentsByField' => $studentsByField,
            'bestStudent' => $bestStudent,
        ]);
    }

    public function search(Request $request)
    {
        if (! $request->has('q') || empty($request->query('q'))) {
            return response()->json(['error' => 'Parameter q is missing or empty'], 400);
        }

        $res = StudentService::search($request->query('q'));

        return response()->json($res);
    }

    public function show($id)
    {
        if (! is_numeric($id)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $student = StudentService::find((int) $id);
        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json($student);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'firstName' => 'required|string|min:2',
                'lastName' => 'required|string|min:2',
                'email' => 'required|email',
                'grade' => 'required|numeric|between:0,20',
                'field' => 'required|in:informatique,mathématiques,physique,chimie',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        }

        // Check unique mock
        if (StudentService::findByEmail($request->input('email'))) {
            return response()->json(['error' => 'Email already exists'], 409);
        }

        $student = StudentService::create($request->all());

        return response()->json($student, 201);
    }

    public function update(Request $request, $id)
    {
        if (! is_numeric($id)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $student = StudentService::find((int) $id);
        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        try {
            $validated = $request->validate([
                'firstName' => 'required|string|min:2',
                'lastName' => 'required|string|min:2',
                'email' => 'required|email',
                'grade' => 'required|numeric|between:0,20',
                'field' => 'required|in:informatique,mathématiques,physique,chimie',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        }

        if (StudentService::findByEmail($request->input('email'), (int) $id)) {
            return response()->json(['error' => 'Email already exists for another student'], 409);
        }

        $updated = StudentService::update((int) $id, $request->all());

        return response()->json($updated);
    }

    public function destroy($id)
    {
        if (! is_numeric($id)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        if (! StudentService::delete((int) $id)) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json(['message' => 'Deleted successfully']);
    }
}
