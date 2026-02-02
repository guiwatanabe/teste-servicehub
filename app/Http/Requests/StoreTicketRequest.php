<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Ticket::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        /** @var \App\Models\UserProfile $userProfile */
        $userProfile = $user->userProfile;
        $companyId = $userProfile->company_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where('company_id', $companyId),
            ],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'assigned_to' => [
                'required',
                Rule::exists('user_profiles', 'user_id')->where('company_id', $companyId),
            ],
            'attachment' => ['nullable', 'file', 'max:4096', 'mimes:json,txt'],
        ];
    }
}
