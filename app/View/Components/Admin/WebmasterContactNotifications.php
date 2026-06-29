<?php
namespace App\View\Components\Admin;

use Illuminate\View\Component;
use App\Models\WebmasterContact;
use App\Support\MemberPortalLayout;

class WebmasterContactNotifications extends Component
{
    public $unreadCount;
    public $latestContacts;

    public function __construct()
    {
        $this->unreadCount = WebmasterContact::where('is_read', false)->count();
        $this->latestContacts = WebmasterContact::query()
            ->with('facility')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
    }

    public function shouldRender(): bool
    {
        return MemberPortalLayout::userIsSystemAdmin(auth()->user());
    }

    public function render()
    {
        return view('components.admin.webmaster-contact-notifications');
    }
}
