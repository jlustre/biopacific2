<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class BlogForm extends Component
{
    public $blogId;
    public $title, $content, $is_global = true, $facility_id, $author, $status = 'draft', $photo1, $photo2, $is_active = true, $version = '1.0', $published_at;

    public function mount($blogId = null)
    {
        if ($blogId) {
            $blog = \App\Models\Blog::findOrFail($blogId);
            $this->blogId = $blog->id;
            $this->title = $blog->title;
            $this->content = $blog->content;
            $this->is_global = $blog->is_global;
            $this->facility_id = $blog->facility_id;
            $this->author = $blog->author;
            $this->status = $blog->status;
            $this->photo1 = $blog->photo1;
            $this->photo2 = $blog->photo2;
            $this->is_active = $blog->is_active;
            $this->version = $blog->version;
            $this->published_at = $blog->published_at;
        }
    }

    public function save()
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'is_global' => 'boolean',
            'facility_id' => 'nullable|exists:facilities,id',
            'author' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'photo1' => 'nullable|string|max:255',
            'photo2' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'version' => 'nullable|string|max:20',
            'published_at' => 'nullable|date',
        ]);
        if ($this->blogId) {
            $blog = \App\Models\Blog::findOrFail($this->blogId);
            $blog->update($data);
            session()->flash('success', 'Blog updated successfully.');
        } else {
            \App\Models\Blog::create($data);
            session()->flash('success', 'Blog created successfully.');
        }
        return redirect()->route('admin.blogs.index');
    }

    public function render()
    {
        return view('livewire.admin.blog-form');
    }
}
