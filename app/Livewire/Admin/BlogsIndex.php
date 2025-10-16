<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Blog;

class BlogsIndex extends Component
{
    public $blogs;

    public function mount()
    {
        $this->blogs = Blog::orderByDesc('created_at')->get();
    }

    public function delete($id)
    {
        Blog::findOrFail($id)->delete();
        $this->blogs = Blog::orderByDesc('created_at')->get();
        session()->flash('success', 'Blog deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.blogs-index');
    }
}
