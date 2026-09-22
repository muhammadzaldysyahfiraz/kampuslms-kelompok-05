<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;

class CourseController extends Controller
{
    // Menampilkan semua mata kuliah
    public function index()
    {
        $courses = Course::with('lecturer')->latest()->get();

        return view('courses.index', compact('courses'));
    }

    // Menampilkan form tambah mata kuliah
    public function create()
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.create', compact('lecturers'));
    }

    // Menyimpan mata kuliah baru
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        Course::create($data);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    // Menampilkan detail mata kuliah
    public function show(Course $course)
    {
        $course->load(['lecturer', 'materials', 'assignments']);
        $course->loadCount('students');

        return view('courses.show', compact('course'));
    }

    // Menampilkan form edit
    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'lecturers'));
    }

    // Memperbarui mata kuliah
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    // Menghapus mata kuliah
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}