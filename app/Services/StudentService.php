<?php

namespace App\Services;

class StudentService
{
    private static array $students = [];

    private static int $autoIncrement = 1;

    public static function init(): void
    {
        self::$students = [
            [
                'id' => self::$autoIncrement++,
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
                'grade' => 15,
                'field' => 'informatique',
            ],
            [
                'id' => self::$autoIncrement++,
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'email' => 'jane.smith@example.com',
                'grade' => 18,
                'field' => 'mathématiques',
            ],
            [
                'id' => self::$autoIncrement++,
                'firstName' => 'Alice',
                'lastName' => 'Johnson',
                'email' => 'alice.j@example.com',
                'grade' => 12,
                'field' => 'physique',
            ],
            [
                'id' => self::$autoIncrement++,
                'firstName' => 'Bob',
                'lastName' => 'Brown',
                'email' => 'bob.b@example.com',
                'grade' => 9,
                'field' => 'chimie',
            ],
            [
                'id' => self::$autoIncrement++,
                'firstName' => 'Charlie',
                'lastName' => 'Davis',
                'email' => 'charlie.d@example.com',
                'grade' => 16,
                'field' => 'informatique',
            ],
        ];
    }

    public static function reset(): void
    {
        self::$autoIncrement = 1;
        self::init();
    }

    public static function all(): array
    {
        if (empty(self::$students)) {
            self::init();
        }

        return self::$students;
    }

    public static function find(int $id): ?array
    {
        foreach (self::all() as $student) {
            if ($student['id'] === $id) {
                return $student;
            }
        }

        return null;
    }

    public static function create(array $data): array
    {
        self::all(); // Ensure init
        $student = [
            'id' => self::$autoIncrement++,
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'grade' => (float) $data['grade'],
            'field' => $data['field'],
        ];
        self::$students[] = $student;

        return $student;
    }

    public static function update(int $id, array $data): ?array
    {
        foreach (self::all() as $k => $student) {
            if ($student['id'] === $id) {
                $updated = array_merge($student, $data);
                self::$students[$k] = $updated;

                return $updated;
            }
        }

        return null;
    }

    public static function delete(int $id): bool
    {
        foreach (self::all() as $k => $student) {
            if ($student['id'] === $id) {
                unset(self::$students[$k]);
                // Reindex array
                self::$students = array_values(self::$students);

                return true;
            }
        }

        return false;
    }

    public static function findByEmail(string $email, ?int $excludeId = null): ?array
    {
        foreach (self::all() as $student) {
            if ($student['email'] === $email && $student['id'] !== $excludeId) {
                return $student;
            }
        }

        return null;
    }

    public static function search(string $q): array
    {
        $q = strtolower($q);

        return array_values(array_filter(self::all(), function ($student) use ($q) {
            return str_contains(strtolower($student['firstName']), $q) ||
                   str_contains(strtolower($student['lastName']), $q);
        }));
    }
}
