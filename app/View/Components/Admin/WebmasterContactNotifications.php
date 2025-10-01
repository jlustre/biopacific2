<?php
namespace App\View\Components\Admin;

use Illuminate\View\Component;
use App\Models\WebmasterContact;

class WebmasterContactNotifications extends Component
{
    public $unreadCount;
    public $latestContacts;

    public function __construct()
    {
        $this->unreadCount = WebmasterContact::where('is_read', false)->count();
        $this->latestContacts = WebmasterContact::orderByDesc('created_at')->take(5)->get();
    }

    public function render()
    {
        return view('components.admin.webmaster-contact-notifications');
    }
}
