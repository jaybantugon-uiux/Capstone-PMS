<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DailyExpenditure;
use App\Models\FinancialReport;
use App\Models\LiquidatedForm;
use App\Models\Receipt;
use App\Models\User;

class ExpenseLiquidationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $type;
    protected $data;
    protected $recipients;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, array $data, $recipients = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->recipients = $recipients;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Hello ' . $notifiable->name . ',');

        switch ($this->type) {
            case 'expenditure_submitted':
                $message->line('A new daily expenditure has been submitted.')
                    ->line('Project: ' . $this->data['project_name'])
                    ->line('Amount: $' . number_format($this->data['amount'], 2))
                    ->line('Description: ' . $this->data['description'])
                    ->action('View Expenditure', $this->data['view_url']);
                break;

            case 'expenditure_approved':
                $message->line('Your daily expenditure has been approved.')
                    ->line('Project: ' . $this->data['project_name'])
                    ->line('Amount: $' . number_format($this->data['amount'], 2))
                    ->line('Approved by: ' . $this->data['approver_name'])
                    ->action('View Expenditure', $this->data['view_url']);
                break;

            case 'expenditure_rejected':
                $message->line('Your daily expenditure has been rejected.')
                    ->line('Project: ' . $this->data['project_name'])
                    ->line('Amount: $' . number_format($this->data['amount'], 2))
                    ->line('Rejected by: ' . $this->data['rejecter_name'])
                    ->line('Reason: ' . $this->data['rejection_reason'])
                    ->action('View Expenditure', $this->data['view_url']);
                break;

            case 'financial_report_created':
                $message->line('A new financial report has been created.')
                    ->line('Title: ' . $this->data['title'])
                    ->line('Period: ' . $this->data['period'])
                    ->line('Total Amount: $' . number_format($this->data['total_amount'], 2))
                    ->action('View Report', $this->data['view_url']);
                break;

            case 'financial_report_approved':
                $message->line('A financial report has been approved.')
                    ->line('Title: ' . $this->data['title'])
                    ->line('Approved by: ' . $this->data['approver_name'])
                    ->line('A liquidated form has been automatically created.')
                    ->action('View Liquidated Form', $this->data['liquidated_form_url']);
                break;

            case 'liquidated_form_created':
                $message->line('A new liquidated form has been created.')
                    ->line('Form Number: ' . $this->data['form_number'])
                    ->line('Title: ' . $this->data['title'])
                    ->line('Total Amount: $' . number_format($this->data['total_amount'], 2))
                    ->action('View Liquidated Form', $this->data['view_url']);
                break;

            case 'liquidated_form_flagged':
                $message->line('A liquidated form has been flagged for suspicious activity.')
                    ->line('Form Number: ' . $this->data['form_number'])
                    ->line('Flagged by: ' . $this->data['flagger_name'])
                    ->line('Reason: ' . $this->data['flag_reason'])
                    ->action('View Liquidated Form', $this->data['view_url']);
                break;

            case 'revision_requested':
                $message->line('A revision has been requested for a liquidated form.')
                    ->line('Form Number: ' . $this->data['form_number'])
                    ->line('Requested by: ' . $this->data['requester_name'])
                    ->line('Reason: ' . $this->data['revision_reason'])
                    ->action('View Liquidated Form', $this->data['view_url']);
                break;

            case 'clarification_requested':
                if (isset($this->data['form_number'])) {
                    // Liquidated form clarification
                    $message->line('A clarification request has been made for a liquidated form.')
                        ->line('Form Number: ' . $this->data['form_number'])
                        ->line('Requested by: ' . ($this->data['requester_name'] ?? 'Admin'))
                        ->line('Question: ' . ($this->data['clarification_question'] ?? 'No question provided'))
                        ->line('Please review the form and take appropriate action as needed.')
                        ->action('View Liquidated Form', $this->data['view_url'] ?? '#');
                } else {
                    // Receipt clarification
                    $message->line('A clarification request has been made for a receipt.')
                        ->line('Receipt Number: ' . ($this->data['receipt_number'] ?? 'N/A'))
                        ->line('Vendor: ' . ($this->data['vendor_name'] ?? 'N/A'))
                        ->line('Requested by: ' . ($this->data['requester_name'] ?? 'Admin'))
                        ->line('Question: ' . ($this->data['clarification_question'] ?? 'No question provided'))
                        ->line('Please review the receipt and take appropriate action as needed.')
                        ->action('View Receipt', $this->data['view_url'] ?? '#');
                }
                break;



            case 'receipt_uploaded':
                $message->line('A new receipt has been uploaded.')
                    ->line('Vendor: ' . $this->data['vendor_name'])
                    ->line('Amount: $' . number_format($this->data['amount'], 2))
                    ->line('Receipt Type: ' . $this->data['receipt_type'])
                    ->action('View Receipt', $this->data['view_url']);
                break;

            case 'receipt_verified':
                $message->line('A receipt has been verified.')
                    ->line('Vendor: ' . $this->data['vendor_name'])
                    ->line('Amount: $' . number_format($this->data['amount'], 2))
                    ->line('Verified by: ' . $this->data['verifier_name'])
                    ->action('View Receipt', $this->data['view_url']);
                break;

            case 'receipt_rejected':
                $message->line('A receipt has been rejected.')
                    ->line('Vendor: ' . $this->data['vendor_name'])
                    ->line('Amount: $' . number_format($this->data['amount'], 2))
                    ->line('Rejected by: ' . $this->data['rejecter_name'])
                    ->line('Reason: ' . $this->data['rejection_reason'])
                    ->action('View Receipt', $this->data['view_url']);
                break;

            case 'bulk_action_completed':
                $message->line('A bulk action has been completed.')
                    ->line('Action: ' . $this->data['action'])
                    ->line('Total Items: ' . $this->data['total_items'])
                    ->line('Successful: ' . $this->data['successful_count'])
                    ->line('Failed: ' . $this->data['failed_count'])
                    ->line('Success Rate: ' . $this->data['success_rate'] . '%');
                break;

            default:
                $message->line('You have a new notification from the Expense Liquidation System.');
        }

        return $message->salutation('Best regards,')
                      ->line('Expense Liquidation System');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
            'read_at' => null
        ];
    }

    /**
     * Get the notification subject.
     */
    protected function getSubject(): string
    {
        switch ($this->type) {
            case 'expenditure_submitted':
                return 'New Daily Expenditure Submitted';
            case 'expenditure_approved':
                return 'Daily Expenditure Approved';
            case 'expenditure_rejected':
                return 'Daily Expenditure Rejected';
            case 'financial_report_created':
                return 'New Financial Report Created';
            case 'financial_report_approved':
                return 'Financial Report Approved - Liquidated Form Created';
            case 'liquidated_form_created':
                return 'New Liquidated Form Created';
            case 'liquidated_form_flagged':
                return 'Liquidated Form Flagged for Review';
            case 'revision_requested':
                return 'Revision Requested for Liquidated Form';
            case 'clarification_requested':
                return 'Clarification Request Made - Liquidated Form Review Required';

            case 'receipt_uploaded':
                return 'New Receipt Uploaded';
            case 'receipt_verified':
                return 'Receipt Verified';
            case 'receipt_rejected':
                return 'Receipt Rejected';
            case 'bulk_action_completed':
                return 'Bulk Action Completed';
            default:
                return 'Expense Liquidation System Notification';
        }
    }

    /**
     * Send notification to specific users based on role
     */
    public static function sendToRole(string $type, array $data, string $role): void
    {
        $users = User::where('role', $role)->get();
        
        foreach ($users as $user) {
            $user->notify(new self($type, $data));
        }
    }

    /**
     * Send notification to project manager
     */
    public static function sendToProjectManager(string $type, array $data, int $projectId): void
    {
        $project = \App\Models\Project::find($projectId);
        if ($project && $project->project_manager) {
            $project->project_manager->notify(new self($type, $data));
        }
    }

    /**
     * Send notification to finance team
     */
    public static function sendToFinance(string $type, array $data): void
    {
        self::sendToRole($type, $data, 'finance');
    }

    /**
     * Send notification to admin team
     */
    public static function sendToAdmin(string $type, array $data): void
    {
        self::sendToRole($type, $data, 'admin');
    }

    /**
     * Send notification to all relevant parties
     */
    public static function sendToAllRelevant(string $type, array $data, int $projectId = null): void
    {
        // Send to finance and admin
        self::sendToFinance($type, $data);
        self::sendToAdmin($type, $data);

        // Send to project manager if project is specified
        if ($projectId) {
            self::sendToProjectManager($type, $data, $projectId);
        }
    }

    /**
     * Create notification data for expenditure submission
     */
    public static function createExpenditureSubmissionData(DailyExpenditure $expenditure): array
    {
        return [
            'project_name' => $expenditure->project ? $expenditure->project->name : 'N/A',
            'amount' => $expenditure->amount,
            'description' => $expenditure->description,
            'submitter_name' => $expenditure->submitter->name,
            'submission_date' => $expenditure->created_at->format('Y-m-d H:i:s'),
            'view_url' => route('finance.expenditures.show', $expenditure->id)
        ];
    }

    /**
     * Create notification data for expenditure approval/rejection
     */
    public static function createExpenditureActionData(DailyExpenditure $expenditure, User $actor, string $action, string $reason = null): array
    {
        $data = [
            'project_name' => $expenditure->project ? $expenditure->project->name : 'N/A',
            'amount' => $expenditure->amount,
            'description' => $expenditure->description,
            'action_date' => now()->format('Y-m-d H:i:s'),
            'view_url' => route('finance.expenditures.show', $expenditure->id)
        ];

        if ($action === 'approved') {
            $data['approver_name'] = $actor->name;
        } else {
            $data['rejecter_name'] = $actor->name;
            $data['rejection_reason'] = $reason;
        }

        return $data;
    }

    /**
     * Create notification data for financial report
     */
    public static function createFinancialReportData(FinancialReport $financialReport): array
    {
        return [
            'title' => $financialReport->title,
            'period' => $financialReport->formatted_period,
            'total_amount' => $financialReport->total_expenditures,
            'creator_name' => $financialReport->creator->name,
            'view_url' => route('finance.reports.show', $financialReport->id)
        ];
    }

    /**
     * Create notification data for liquidated form
     */
    public static function createLiquidatedFormData(LiquidatedForm $liquidatedForm): array
    {
        return [
            'form_number' => $liquidatedForm->form_number,
            'title' => $liquidatedForm->title,
            'total_amount' => $liquidatedForm->total_amount,
            'preparer_name' => $liquidatedForm->preparer->name,
            'view_url' => route('finance.liquidated-forms.show', $liquidatedForm->id)
        ];
    }

    /**
     * Create notification data for receipt
     */
    public static function createReceiptData(Receipt $receipt): array
    {
        return [
            'vendor_name' => $receipt->vendor_name,
            'amount' => $receipt->amount,
            'receipt_type' => $receipt->receipt_type,
            'uploader_name' => $receipt->uploader->name,
            'view_url' => route('finance.receipts.show', $receipt->id)
        ];
    }
}
