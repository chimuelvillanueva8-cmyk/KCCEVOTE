<?php

use App\Controllers\students\Student;
use PHPUnit\Framework\TestCase;

final class StudentImportControllerForTesting extends Student
{
    public function __construct()
    {
    }

    public function mapImportHeader(string $header, array $fieldMap): ?string
    {
        return $this->importFieldForHeader($header, $fieldMap);
    }

    public function appendRow(array &$rows, array $headers, array $values): void
    {
        $this->appendImportRow($rows, $headers, $values);
    }
}

final class StudentImportParsingTest extends TestCase
{
    public function testCommonStudentIdAndCourseHeadersAreRecognized(): void
    {
        $controller = new StudentImportControllerForTesting();
        $fieldMap = [
            'student id' => 'student_id',
            'student id no' => 'student_id',
            'student number' => 'student_id',
            'course name' => 'course',
        ];

        self::assertSame('student_id', $controller->mapImportHeader('Student_ID', $fieldMap));
        self::assertSame('student_id', $controller->mapImportHeader('Student ID No.', $fieldMap));
        self::assertSame('student_id', $controller->mapImportHeader('ID Number', $fieldMap));
        self::assertSame('course', $controller->mapImportHeader('Course Name', $fieldMap));
    }

    public function testImportRowsArePaddedToHeadersAndBlankRowsSkipped(): void
    {
        $controller = new StudentImportControllerForTesting();
        $rows = [];

        $controller->appendRow($rows, ['student_id', 'full_name', 'course'], ['12345', 'Ada']);
        $controller->appendRow($rows, ['student_id', 'full_name', 'course'], ['', '', '']);

        self::assertSame([
            ['student_id' => '12345', 'full_name' => 'Ada', 'course' => ''],
        ], $rows);
    }
}
