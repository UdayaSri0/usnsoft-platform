<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            title="Submit a Project Request"
            description="Verified account intake for project ideas, quotations, meetings, and scoped implementation work."
            eyebrow="Client Requests"
        />
    </x-slot>

    <div class="py-8">
        <div class="usn-container-wide space-y-6">
            <x-ui.alert tone="info" title="Protected intake">
                Submissions stay tied to your account, attachments are stored privately, and staff updates are handled through audited workflow actions.
            </x-ui.alert>

            @if ($errors->any())
                <x-ui.alert tone="warning" title="Please review the highlighted fields">
                    Fix the validation issues below and submit the request again.
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('client-requests.store') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                @csrf

                <section class="usn-card space-y-5">
                    <div>
                        <h2 class="font-display text-2xl font-semibold text-slate-950">Project details</h2>
                        <p class="mt-2 text-sm text-slate-600">Start with the business problem, expected outcome, and the delivery shape you need.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="requester_name" value="Requester Name" />
                            <x-text-input id="requester_name" name="requester_name" class="mt-2 block w-full" :value="old('requester_name', $user->name)" required />
                            <x-input-error :messages="$errors->get('requester_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="project_type" value="Request Type" />
                            <x-select-input id="project_type" name="project_type" class="mt-2 block w-full" required>
                                <option value="">Select a request type</option>
                                @foreach ($projectTypes as $projectType)
                                    <option value="{{ $projectType->value }}" @selected(old('project_type') === $projectType->value)>{{ $projectType->label() }}</option>
                                @endforeach
                            </x-select-input>
                            <x-input-error :messages="$errors->get('project_type')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="company_name" value="Company Name" />
                            <x-text-input id="company_name" name="company_name" class="mt-2 block w-full" :value="old('company_name')" />
                            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="project_title" value="Project Title" />
                            <x-text-input id="project_title" name="project_title" class="mt-2 block w-full" :value="old('project_title')" required />
                            <x-input-error :messages="$errors->get('project_title')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="project_summary" value="Project Summary" />
                        <x-textarea-input id="project_summary" name="project_summary" rows="3" class="mt-2 block w-full" required>{{ old('project_summary') }}</x-textarea-input>
                        <x-input-error :messages="$errors->get('project_summary')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="project_description" value="Project Description" />
                        <x-textarea-input id="project_description" name="project_description" rows="10" class="mt-2 block w-full" required>{{ old('project_description') }}</x-textarea-input>
                        <x-input-error :messages="$errors->get('project_description')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="budget" value="Budget" />
                            <x-text-input id="budget" name="budget" type="number" min="0" step="0.01" class="mt-2 block w-full" :value="old('budget')" />
                            <x-input-error :messages="$errors->get('budget')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="deadline" value="Preferred Deadline" />
                            <x-text-input id="deadline" name="deadline" type="date" class="mt-2 block w-full" :value="old('deadline')" />
                            <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <div class="space-y-6">
                    <section class="usn-card space-y-5">
                        <div>
                            <h2 class="font-display text-xl font-semibold text-slate-950">Contact and delivery context</h2>
                            <p class="mt-2 text-sm text-slate-600">These fields help staff route the request correctly and plan the initial discussion.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="contact_email" value="Contact Email" />
                                <x-text-input id="contact_email" name="contact_email" type="email" class="mt-2 block w-full" :value="old('contact_email', $user->email)" required />
                                <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="contact_phone" value="Contact Phone" />
                                <x-text-input id="contact_phone" name="contact_phone" class="mt-2 block w-full" :value="old('contact_phone', $user->phone)" />
                                <x-input-error :messages="$errors->get('contact_phone')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="requested_features" value="Requested Features" />
                            <x-textarea-input id="requested_features" name="requested_features" rows="4" class="mt-2 block w-full">{{ old('requested_features') }}</x-textarea-input>
                            <p class="mt-2 text-xs text-slate-500">Use commas or new lines to describe features, integrations, or requirements.</p>
                            <x-input-error :messages="$errors->get('requested_features')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="preferred_tech_stack" value="Preferred Tech Stack" />
                            <x-textarea-input id="preferred_tech_stack" name="preferred_tech_stack" rows="4" class="mt-2 block w-full">{{ old('preferred_tech_stack') }}</x-textarea-input>
                            <x-input-error :messages="$errors->get('preferred_tech_stack')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="preferred_meeting_availability" value="Preferred Meeting Availability" />
                            <x-textarea-input id="preferred_meeting_availability" name="preferred_meeting_availability" rows="4" class="mt-2 block w-full">{{ old('preferred_meeting_availability') }}</x-textarea-input>
                            <x-input-error :messages="$errors->get('preferred_meeting_availability')" class="mt-2" />
                        </div>
                    </section>

                    <section class="usn-card space-y-5">
                        <div>
                            <h2 class="font-display text-xl font-semibold text-slate-950">Supporting files</h2>
                            <p class="mt-2 text-sm text-slate-600">Upload relevant screenshots, PDFs, scope documents, or voice notes. Files stay protected and are never published directly.</p>
                        </div>

                        <div>
                            <x-input-label for="attachments" value="Attachments" />
                            <input id="attachments" name="attachments[]" type="file" multiple class="usn-input mt-2 block w-full file:mr-4 file:rounded-2xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white" />
                            <p class="mt-2 text-xs text-slate-500">Allowed types: {{ $allowedExtensions }}. Maximum {{ $maxUploadMb }} MB per file, up to 10 files.</p>
                            <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                            <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />
                        </div>
                    </section>

                    <section class="usn-card">
                        <h2 class="font-display text-xl font-semibold text-slate-950">Before you submit</h2>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                            <li>Use internal or private document links only through attachments, not public URLs.</li>
                            <li>Status changes and requester-visible updates will appear in your account timeline.</li>
                            <li>Sales Manager and SuperAdmin receive the initial in-app notification automatically.</li>
                        </ul>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <button type="submit" class="usn-btn-primary">Submit Request</button>
                            <a href="{{ route('client-requests.index') }}" class="usn-btn-secondary">View My Requests</a>
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
