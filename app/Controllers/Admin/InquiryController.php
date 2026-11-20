<?php
namespace App\Controllers\Admin;

use App\Models\Inquiry;
use Core\Controller;
use Core\Request;
use Core\Response;

class InquiryController extends Controller
{
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', '');

        return $this->view('admin/inquiries/index', [
            'title'     => 'Adoption inquiries',
            'inquiries' => Inquiry::adminList($status),
            'status'    => $status,
        ], 'layouts/admin');
    }

    public function updateStatus(Request $request, string $id): Response
    {
        Inquiry::findOrFail((int) $id);

        $status = (string) $request->input('status');
        if (!in_array($status, ['new', 'contacted', 'closed'], true)) {
            $this->flash('error', 'Unknown status.');
            return $this->back($request, '/admin/inquiries');
        }

        Inquiry::update((int) $id, ['status' => $status]);
        $this->flash('success', 'Inquiry marked as "' . $status . '".');
        return $this->back($request, '/admin/inquiries');
    }
}
