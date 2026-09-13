<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'unique:courses,code'],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
            'lecturer_id' => ['required', 'exists:users,id'],
        ]);

        $data['status'] = 'draft';

        Course::create($data);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    // Menampilkan detail mata kuliah
    public function show(Course $course)
    {
        $course->load('lecturer');

        return view('courses.show', compact('course'));
    }

    // Menampilkan form edit
    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->get();

        return view('courses.edit', compact('course', 'lecturers'));
    }

    // Memperbarui mata kuliah
    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'unique:courses,code,' . $course->id,
            ],
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
            'lecturer_id' => ['required', 'exists:users,id'],
        ]);

        $course->update($data);

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