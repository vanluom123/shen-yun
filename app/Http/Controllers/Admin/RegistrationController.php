<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\Venue;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    private function excelEscape(mixed $value): string
    {
        $s = (string) ($value ?? '');
        $s = preg_replace("/\\R/u", ' ', $s) ?? $s;

        // Prevent Excel formula injection
        if ($s !== '' && preg_match('/^[=+\\-@]/', $s)) {
            $s = "'".$s;
        }

        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function index(Request $request)
    {
        $status    = $request->query('status') ?: null;
        $search    = $request->query('search') ?: null;

        $sessions = EventSession::query()
            ->with('venue')
            ->orderBy('starts_at', 'desc')
            ->get();

        $sessionIdParam = $request->query('session_id'); // null = no param, 'all' = tất cả, else = id

        if ($sessionIdParam === null) {
            // Default: first session in current week
            $weekStart = now()->startOfWeek();
            $weekEnd   = now()->endOfWeek();
            $default   = $sessions->first(fn($s) => $s->starts_at->between($weekStart, $weekEnd));
            $sessionId = $default ? (string) $default->id : null;
        } elseif ($sessionIdParam === 'all') {
            $sessionId = null; // no filter
        } else {
            $sessionId = $sessionIdParam;
        }

        $query = Registration::query()
            ->join('event_sessions', 'registrations.event_session_id', '=', 'event_sessions.id')
            ->select('registrations.*')
            ->with('eventSession.venue')
            ->orderByDesc('event_sessions.starts_at');

        if ($status && in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            $query->where('registrations.status', $status);
        }

        if ($sessionId) {
            $query->where('registrations.event_session_id', $sessionId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('registrations.phone', 'like', "%{$search}%");
                if (str_starts_with($search, '0')) {
                    $q->orWhere('registrations.phone', 'like', '%+84' . substr($search, 1) . '%');
                } elseif (str_starts_with($search, '+84')) {
                    $q->orWhere('registrations.phone', 'like', '%0' . substr($search, 3) . '%');
                }
                $q->orWhere('registrations.full_name', 'like', "%{$search}%");
            });
        }

        $regs = $query->paginate(30)->withQueryString();

        return view('admin.registrations.index', [
            'registrations'   => $regs,
            'statusFilter'    => $status,
            'sessionIdFilter' => $sessionIdParam ?? $sessionId, // 'all', specific id, or default id
            'searchFilter'    => $search,
            'sessions'        => $sessions,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Registration::query()
            ->join('event_sessions', 'registrations.event_session_id', '=', 'event_sessions.id')
            ->select('registrations.*')
            ->with('eventSession.venue')
            ->orderByDesc('event_sessions.starts_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            $query->where('registrations.status', $status);
        }

        $sessionId = $request->query('session_id');
        if ($sessionId) {
            $query->where('registrations.event_session_id', $sessionId);
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('registrations.phone', 'like', "%{$search}%");
                
                if (str_starts_with($search, '0')) {
                    $alt = '+84' . substr($search, 1);
                    $q->orWhere('registrations.phone', 'like', "%{$alt}%");
                } elseif (str_starts_with($search, '+84')) {
                    $alt = '0' . substr($search, 3);
                    $q->orWhere('registrations.phone', 'like', "%{$alt}%");
                }
                
                $q->orWhere('registrations.full_name', 'like', "%{$search}%");
            });
        }

        $filename = 'registrations.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            $delimiter = ',';
            $eol = "\r\n";

            // Excel reliably reads UTF-16LE CSV with BOM (fixes Vietnamese mojibake)
            fwrite($out, "\xFF\xFE");

            $writeLine = function (string $line) use ($out) {
                $encoded = iconv('UTF-8', 'UTF-16LE//IGNORE', $line);
                if ($encoded !== false) {
                    fwrite($out, $encoded);
                }
            };

            $writeRow = function (array $row) use ($delimiter, $eol, $writeLine) {
                $tmp = fopen('php://temp', 'r+');
                fputcsv($tmp, $row, $delimiter, '"', '\\', $eol);
                rewind($tmp);
                $line = stream_get_contents($tmp);
                fclose($tmp);
                $writeLine($line !== false ? $line : '');
            };

            // Hint Excel which delimiter to use
            $writeLine("sep=,{$eol}");

            $writeRow(
                [
                    'Mã',
                    'Tạo lúc',
                    'Địa điểm',
                    'Trình chiếu',
                    'Họ tên',
                    'Phone',
                    'Khách',
                    'NTL',
                    'NTL mới',
                    'Trẻ em',
                    'Tổng',
                    'Đi cùng khách',
                    'Trạng thái',
                ]
            );

            $query->chunk(500, function ($rows) use ($writeRow) {
                foreach ($rows as $r) {
                    $writeRow(
                        [
                            $r->id,
                            $r->created_at?->format('d/m/Y H:i'),
                            $r->eventSession?->venue?->name,
                            $r->eventSession?->starts_at?->format('d/m/Y H:i'),
                            $r->full_name,
                            $r->phone,
                            $r->adult_count,
                            $r->ntl_count,
                            $r->ntl_new_count,
                            $r->child_count,
                            $r->total_count,
                            $r->attend_with_guest ? 'Có' : 'Không',
                            $r->status,
                        ]
                    );
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-16LE',
        ]);
    }

    public function exportXls(Request $request): StreamedResponse
    {
        $query = Registration::query()
            ->join('event_sessions', 'registrations.event_session_id', '=', 'event_sessions.id')
            ->select('registrations.*')
            ->with('eventSession.venue')
            ->orderByDesc('event_sessions.starts_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'confirmed', 'cancelled'])) {
            $query->where('registrations.status', $status);
        }

        $sessionId = $request->query('session_id');
        if ($sessionId) {
            $query->where('registrations.event_session_id', $sessionId);
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('registrations.phone', 'like', "%{$search}%");
                
                if (str_starts_with($search, '0')) {
                    $alt = '+84' . substr($search, 1);
                    $q->orWhere('registrations.phone', 'like', "%{$alt}%");
                } elseif (str_starts_with($search, '+84')) {
                    $alt = '0' . substr($search, 3);
                    $q->orWhere('registrations.phone', 'like', "%{$alt}%");
                }
                
                $q->orWhere('registrations.full_name', 'like', "%{$search}%");
            });
        }

        $filename = 'registrations.xls';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            fwrite($out, "<!doctype html>\n");
            fwrite($out, "<html><head><meta charset=\"UTF-8\">\n");
            fwrite($out, "<style>
                td,th{border:1px solid #ddd;padding:7px 10px;font-family:Arial;font-size:12px;vertical-align:top}
                th{background:#f3f4f6;font-weight:700}
                table{border-collapse:collapse;table-layout:fixed}
            </style>\n");
            fwrite($out, "</head><body>\n");
            fwrite($out, "<table>\n");
            // Column widths (Excel tends to respect colgroup widths for HTML .xls)
            fwrite($out, "<colgroup>");
            fwrite($out, "<col style=\"width:60px\">");   // Mã
            fwrite($out, "<col style=\"width:120px\">");  // Tạo lúc
            fwrite($out, "<col style=\"width:160px\">");  // Địa điểm
            fwrite($out, "<col style=\"width:140px\">");  // Trình chiếu
            fwrite($out, "<col style=\"width:180px\">");  // Họ tên
            fwrite($out, "<col style=\"width:140px\">");  // Phone
            fwrite($out, "<col style=\"width:80px\">");   // Khách
            fwrite($out, "<col style=\"width:80px\">");   // NTL
            fwrite($out, "<col style=\"width:80px\">");   // NTL mới
            fwrite($out, "<col style=\"width:80px\">");   // Trẻ em
            fwrite($out, "<col style=\"width:80px\">");   // Tổng
            fwrite($out, "<col style=\"width:120px\">");  // Đi cùng khách
            fwrite($out, "<col style=\"width:100px\">");  // Trạng thái
            fwrite($out, "</colgroup>\n");
            fwrite($out, "<thead><tr>");

            $headers = [
                'Mã',
                'Tạo lúc',
                'Địa điểm',
                'Trình chiếu',
                'Họ tên',
                'Phone',
                'Khách',
                'NTL',
                'NTL mới',
                'Trẻ em',
                'Tổng',
                'Đi cùng khách',
                'Trạng thái',
            ];

            foreach ($headers as $h) {
                fwrite($out, '<th>'.$this->excelEscape($h).'</th>');
            }
            fwrite($out, "</tr></thead>\n<tbody>\n");

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fwrite($out, "<tr>");
                    $cells = [
                        $r->id,
                        $r->created_at?->format('d/m/Y H:i'),
                        $r->eventSession?->venue?->name,
                        $r->eventSession?->starts_at?->format('d/m/Y H:i'),
                        $r->full_name,
                        $r->phone,
                        $r->adult_count,
                        $r->ntl_count,
                        $r->ntl_new_count,
                        $r->child_count,
                        $r->total_count,
                        $r->attend_with_guest ? 'Có' : 'Không',
                        $r->status,
                    ];

                    foreach ($cells as $c) {
                        fwrite($out, '<td>'.$this->excelEscape($c).'</td>');
                    }
                    fwrite($out, "</tr>\n");
                }
            });

            fwrite($out, "</tbody></table>\n</body></html>");
            fclose($out);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function edit(Registration $registration)
    {
        $venues = Venue::query()->orderBy('name')->get();
        $sessions = EventSession::query()
            ->with('venue')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        return view('admin.registrations.edit', [
            'registration' => $registration,
            'venues' => $venues,
            'sessions' => $sessions,
        ]);
    }

    public function update(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{9,11}$/'],
            'event_session_id' => ['required', 'exists:event_sessions,id'],
            'adult_count' => ['required', 'integer', 'min:0', 'max:999'],
            'ntl_count' => ['required', 'integer', 'min:0', 'max:999'],
            'ntl_new_count' => ['required', 'integer', 'min:0', 'max:999'],
            'child_count' => ['required', 'integer', 'min:0', 'max:999'],
            'attend_with_guest' => ['required', 'in:0,1'],
        ]);

        if (!empty($data['phone'])) {
            $data['phone'] = ltrim(preg_replace('/\D+/', '', (string)$data['phone']), '0');
        }

        $data['attend_with_guest'] = (bool) $data['attend_with_guest'];

        $oldSessionId = $registration->event_session_id;
        $oldTotalCount = $registration->total_count;

        $total = (int) $data['adult_count']
            + (int) $data['ntl_count']
            + (int) $data['ntl_new_count']
            + (int) $data['child_count'];

        $data['total_count'] = $total;

        $registration->update($data);

        if ($registration->status === 'confirmed') {
            if ($oldSessionId !== $registration->event_session_id) {
                EventSession::recalculateReserved($oldSessionId);
                EventSession::recalculateReserved($registration->event_session_id);
            } elseif ($oldTotalCount !== $total) {
                EventSession::recalculateReserved($registration->event_session_id);
            }
        }

        $redirectTo = $request->query('back', '/admin/registrations');
        return redirect()->to($redirectTo)->with('success', 'Cập nhật thành công.');
    }

    public function confirm(Registration $registration)
    {
        $wasPending = $registration->status === 'pending';
        $sessionId = $registration->event_session_id;

        $registration->update(['status' => 'confirmed']);

        if ($wasPending) {
            EventSession::recalculateReserved($sessionId);
        }

        $redirectTo = request()->input('redirect_to', '/admin/registrations');
        return redirect()->to($redirectTo)->with('success', 'Đã xác nhận đăng ký.');
    }

    public function cancel(Registration $registration)
    {
        $wasConfirmed = $registration->status === 'confirmed';
        $sessionId = $registration->event_session_id;

        $registration->update(['status' => 'cancelled']);

        if ($wasConfirmed) {
            EventSession::recalculateReserved($sessionId);
        }

        $redirectTo = request()->input('redirect_to', '/admin/registrations');
        return redirect()->to($redirectTo)->with('success', 'Đã hủy đăng ký.');
    }

    public function destroy(Registration $registration)
    {
        $wasConfirmed = $registration->status === 'confirmed';
        $sessionId = $registration->event_session_id;

        $registration->delete();

        if ($wasConfirmed) {
            EventSession::recalculateReserved($sessionId);
        }

        $redirectTo = request()->input('redirect_to', '/admin/registrations');
        return redirect()->to($redirectTo)->with('success', 'Đã xóa đăng ký.');
    }
}
