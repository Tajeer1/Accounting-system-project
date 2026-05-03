<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankEmailIntegration;
use App\Services\BankEmailParsers\ParserResolver;
use App\Services\BankEmailService;
use Illuminate\Http\Request;

class BankEmailIntegrationController extends Controller
{
    public function __construct(
        protected BankEmailService $service,
        protected ParserResolver $parsers,
    ) {}

    public function index()
    {
        $integrations = BankEmailIntegration::with('bankAccount')
            ->orderByDesc('is_active')
            ->orderBy('bank_name')
            ->get();

        return view('bank-emails.integrations.index', compact('integrations'));
    }

    public function create()
    {
        return view('bank-emails.integrations.create', [
            'integration' => null,
            'bankAccounts' => $this->bankAccountOptions(),
            'parserOptions' => $this->parserOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $password = $data['password'] ?? null;
        unset($data['password']);

        $integration = new BankEmailIntegration($data);
        $integration->is_active = $request->boolean('is_active', true);
        $integration->validate_cert = $request->boolean('validate_cert', true);
        $integration->auto_confirm = $request->boolean('auto_confirm', false);
        $integration->mark_seen_after_import = $request->boolean('mark_seen_after_import', true);
        if ($password) {
            $integration->password = $password;
        }
        $integration->save();

        return redirect()->route('bank-emails.integrations.index')
            ->with('success', 'تم إضافة التكامل بنجاح');
    }

    public function show(BankEmailIntegration $integration)
    {
        $integration->load('bankAccount');
        $messages = $integration->messages()->latest()->limit(20)->get();

        return view('bank-emails.integrations.show', compact('integration', 'messages'));
    }

    public function edit(BankEmailIntegration $integration)
    {
        return view('bank-emails.integrations.edit', [
            'integration' => $integration,
            'bankAccounts' => $this->bankAccountOptions(),
            'parserOptions' => $this->parserOptions(),
        ]);
    }

    public function update(Request $request, BankEmailIntegration $integration)
    {
        $data = $this->validateData($request, $integration->id);
        $password = $data['password'] ?? null;
        unset($data['password']);

        $integration->fill($data);
        $integration->is_active = $request->boolean('is_active', true);
        $integration->validate_cert = $request->boolean('validate_cert', true);
        $integration->auto_confirm = $request->boolean('auto_confirm', false);
        $integration->mark_seen_after_import = $request->boolean('mark_seen_after_import', true);
        if (! empty($password)) {
            $integration->password = $password;
        }
        $integration->save();

        return redirect()->route('bank-emails.integrations.index')
            ->with('success', 'تم تحديث التكامل');
    }

    public function destroy(BankEmailIntegration $integration)
    {
        $integration->delete();
        return redirect()->route('bank-emails.integrations.index')
            ->with('success', 'تم حذف التكامل');
    }

    public function testConnection(BankEmailIntegration $integration)
    {
        $result = $this->service->testConnection($integration);

        if ($result['success']) {
            return back()->with('success', 'الاتصال ناجح. عدد المجلدات: ' . ($result['folders'] ?? 0));
        }

        return back()->with('error', 'فشل الاتصال: ' . ($result['error'] ?? 'unknown'));
    }

    public function sync(BankEmailIntegration $integration)
    {
        $stats = $this->service->syncIntegration($integration, 100);

        if (! ($stats['success'] ?? false)) {
            return back()->with('error', 'فشل المزامنة: ' . ($stats['error'] ?? 'unknown'));
        }

        $msg = sprintf(
            'تمت المزامنة: تم جلب %d، تمت معالجة %d، مكرر %d، فشل %d',
            $stats['fetched'] ?? 0,
            $stats['processed'] ?? 0,
            $stats['duplicates'] ?? 0,
            $stats['failed'] ?? 0,
        );

        return back()->with('success', $msg);
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        $rules = [
            'bank_name' => ['required', 'string', 'max:120'],
            'parser_key' => ['required', 'string', 'max:60'],
            'email_address' => ['required', 'email', 'max:190'],
            'imap_host' => ['required', 'string', 'max:190'],
            'imap_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', 'in:ssl,tls,none'],
            'username' => ['required', 'string', 'max:190'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'max:255'],
            'mailbox_folder' => ['nullable', 'string', 'max:120'],
            'sender_filter' => ['nullable', 'string', 'max:190'],
            'keyword_filter' => ['nullable', 'string', 'max:190'],
            'linked_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'is_active' => ['nullable', 'boolean'],
            'validate_cert' => ['nullable', 'boolean'],
            'auto_confirm' => ['nullable', 'boolean'],
            'mark_seen_after_import' => ['nullable', 'boolean'],
        ];

        $data = $request->validate($rules);
        $data['mailbox_folder'] = $data['mailbox_folder'] ?? 'INBOX';
        return $data;
    }

    protected function bankAccountOptions(): array
    {
        return BankAccount::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function parserOptions(): array
    {
        $options = [];
        foreach ($this->parsers->all() as $parser) {
            $options[$parser->key()] = BankEmailIntegration::PARSERS[$parser->key()] ?? $parser->bankName();
        }
        return $options;
    }
}
