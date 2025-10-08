<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, Validate};
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Paparee\BaleNawasara\App\Models\HelpdeskForm;
use Paparee\BaleNawasara\App\Services\WagoService;
use DutchCodingCompany\LivewireRecaptcha\ValidatesRecaptcha;

new
    #[Layout('bale-cms::layouts.guest')]
    #[Title('Rakaca | Bantuan')]
    class extends Component {

    public string $gRecaptchaResponse;

    public $name;
    public $nip;
    public $phone;
    public $description;

    public $sended = false;

    public $enabledWago = false;

    public function mount()
    {
        $this->enabledWago = env('ENABLE_WAGO');
    }

    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:100',
            'nip' => 'required|min:18|max:18',
            'phone' => 'required|min:9|max:18',
            'description' => 'required|min:3|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Mohon isi :attribute ',
            'name.min' => ' :attribute terlalu pendek',
            'name.max' => ' :attribute terlalu panjang',
            'nip.required' => 'Mohon isi :attribute ',
            'nip.min' => ' :attribute terlalu pendek',
            'nip.max' => ' :attribute terlalu panjang',
            'phone.required' => 'Mohon isi :attribute ',
            'phone.min' => ' :attribute terlalu pendek',
            'phone.max' => ' :attribute terlalu panjang',
            'description.required' => 'Mohon isi :attribute ',
            'description.min' => ' :attribute terlalu pendek',
            'description.max' => ' :attribute terlalu panjang',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'name' => 'Nama',
            'nip' => 'NIP',
            'phone' => 'Nomor Whatsapp',
            'description' => 'Deskripsi',
        ];
    }

    #[ValidatesRecaptcha]
    public function store(LivewireAlert $alert)
    {
        $this->validate();
        $this->dispatch('disabling-button', params: true);

        $convert_phone = $this->convertPhoneToWhatsApp($this->phone);

        if ($this->userPhoneCheck($convert_phone)) {

            DB::beginTransaction();

            try {
                $form = HelpdeskForm::create([
                    'name' => $this->name,
                    'nip' => $this->nip,
                    'phone' => $convert_phone,
                    'description' => $this->description,
                    'status' => 'open',
                ]);

                if ($this->enabledWago) {

                    $time = date('d-m-Y H:i:s');

                    $msg = "📢 *Aduan Baru Masuk*
*No. Tiket* : {$form->ticket_number}

*Pelapor* : {$this->name}
*NIP* : {$this->nip}
*Kontak* : https://wa.me/{$convert_phone}
*Isi Aduan* :
_{$this->description}_

*Waktu* : {$time}";

                    //send to aduan group
                    (new WagoService)->sendMessageGroup(env('ADUAN_GROUP_ID'), $msg);

                    $client_msg = "*Aduan anda telah tercatat*
*No. Tiket* : {$form->ticket_number}
*Pelapor* : {$this->name}
*NIP* : {$this->nip}
*Isi Aduan* :
_{$this->description}_

Aduan anda akan ditindaklanjuti petugas kami.
Terima kasih.";

                    //send to client
                    (new WagoService)->sendMessage($convert_phone, $client_msg);
                }

                DB::commit();
                $this->sended = true;

            } catch (\Throwable $th) {
                $this->dispatch('disabling-button', params: false);

                DB::rollBack();
                info($th->getMessage());
                $alert->title('Keluhan gagal disimpan')->position('top-end')->error()->toast()->show();
            }
        } else {
            $this->dispatch('disabling-button', params: false);
            $alert->title('Nomor Whatsapp tidak aktif')->text('Mohon periksa ulang nomor whatsapp anda')->info()->timer(5000)->show();
        }

    }

    protected function convertPhoneToWhatsApp($phoneNumber)
    {
        // Hapus semua karakter non-digit
        $cleaned = preg_replace('/\D/', '', $phoneNumber);

        // Jika diawali dengan '08', ganti dengan '628'
        if (substr($cleaned, 0, 2) === '08') {
            return '62' . substr($cleaned, 1);
        }

        // Jika sudah diawali dengan '62', biarkan as is
        if (substr($cleaned, 0, 2) === '62') {
            return $cleaned;
        }

        // Jika diawali dengan '8', tambahkan '62'
        if (substr($cleaned, 0, 1) === '8') {
            return '62' . $cleaned;
        }

        // Untuk format lainnya, return yang sudah dibersihkan
        return $cleaned;
    }

    protected function userPhoneCheck($convert_phone)
    {
        if ($this->enabledWago) {
            $response = (new WagoService())->userCheck($convert_phone);
            if ($response->successful()) {
                return $response['results']['is_on_whatsapp'];
            }
        } else {
            return true;
        }

    }
}; 
?>

<div>
    <div class="flex items-center justify-center min-h-full px-4 pt-5 sm:px-6 lg:px-8" wire:cloak>
        {{-- <!-- Theme Toggle --> --}}

        <div class="absolute p-0.5 rounded-full bg-emerald-300 right-3 top-3">
            <x-bale.dark-mode-toggle />
        </div>

        <div class="w-full max-w-lg space-y-8" x-data="{isSend: $wire.entangle('sended').live, disabledButton: false}">
            {{-- <!-- Header --> --}}
            <div class="text-center">
                <div
                    class="flex items-center justify-center w-16 h-16 mx-auto mb-4 shadow-lg bg-emerald-500 rounded-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-white lucide lucide-message-circle-question-mark-icon lucide-message-circle-question-mark">
                        <path
                            d="M2.992 16.342a2 2 0 0 1 .094 1.167l-1.065 3.29a1 1 0 0 0 1.236 1.168l3.413-.998a2 2 0 0 1 1.099.092 10 10 0 1 0-4.777-4.719" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <path d="M12 17h.01" />
                    </svg>
                </div>
                <h2 class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">
                    Form Bantuan Layanan
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Sampaikan keluhan Anda dengan mudah
                </p>
            </div>

            {{-- <!-- Form Card --> --}}
            <div
                class="p-6 transition-all duration-300 bg-white shadow-xl dark:bg-gray-800 rounded-2xl sm:p-8 hover:shadow-2xl">

                @if($errors->has('gRecaptchaResponse'))
                    <div class="py-2.5 mb-6 bg-red-200 rounded-lg">
                        <div class="font-semibold text-center text-red-500">Galat!, mohon dicoba beberapa saat lagi</div>
                    </div>
                @endif

                <form wire:submit='store' class="space-y-6" x-show="!isSend" wire:recaptcha>
                    {{-- <!-- Nama Field --> --}}
                    <div>
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nama Lengkap
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="w-4 h-4 text-gray-400 lucide lucide-user-icon lucide-user">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <input type="text" id="nama" wire:model='name'
                                class="block w-full py-3 pl-10 pr-3 text-gray-900 placeholder-gray-500 transition-all duration-200 bg-white border border-gray-300 form-input dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Masukkan nama lengkap Anda" required autocomplete="off">
                        </div>
                        <x-input-error for="name" />
                    </div>

                    {{-- <!-- NIP Field --> --}}
                    <div>
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            NIP
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="w-4 h-4 text-gray-400 lucide lucide-id-card-icon lucide-id-card">
                                    <path d="M16 10h2" />
                                    <path d="M16 14h2" />
                                    <path d="M6.17 15a3 3 0 0 1 5.66 0" />
                                    <circle cx="9" cy="11" r="2" />
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                </svg>
                            </div>
                            <input type="text" id="nip" x-mask="999999999999999999" wire:model='nip'
                                class="block w-full py-3 pl-10 pr-3 text-gray-900 placeholder-gray-500 transition-all duration-200 bg-white border border-gray-300 form-input dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Masukkan NIP Anda" required autocomplete="off">
                        </div>
                        <x-input-error for="nip" />
                    </div>

                    {{-- <!-- WhatsApp Field --> --}}
                    <div>
                        <label for="whatsapp" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nomor WhatsApp
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="w-4 h-4 text-gray-400 lucide lucide-phone-icon lucide-phone">
                                    <path
                                        d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384" />
                                </svg>
                            </div>
                            <input type="text" wire:model='phone' x-mask="99999999999999"
                                class="block w-full py-3 pl-10 pr-3 text-gray-900 placeholder-gray-500 transition-all duration-200 bg-white border border-gray-300 form-input dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Contoh: 081234567890" required autocomplete="off">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Kami membutuhkan nomor whatsapp yang aktif untuk menghubungi anda
                        </p>
                        <x-input-error for="phone" />
                    </div>

                    {{-- <!-- Deskripsi Field --> --}}
                    <div>
                        <label for="deskripsi" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Deskripsi Keluhan
                        </label>
                        <div class="relative">
                            <div class="absolute pointer-events-none top-3 left-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="w-4 h-4 text-gray-400 lucide lucide-message-square-icon lucide-message-square">
                                    <path
                                        d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z" />
                                </svg>
                            </div>
                            <textarea id="deskripsi" rows="5" wire:model='description'
                                class="block w-full py-3 pl-10 pr-3 text-gray-900 placeholder-gray-500 transition-all duration-200 bg-white border border-gray-300 resize-none form-input dark:border-gray-600 rounded-xl dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                placeholder="Jelaskan keluhan atau pertanyaan Anda secara detail..." required
                                autocomplete="off">
                                </textarea>
                        </div>
                        <x-input-error for="description" />
                    </div>

                    {{-- <!-- Submit Button --> --}}
                    <button type="submit" :disabled="disabledButton" wire:loading.attr="disabled"
                        x-on:disabling-button.window="disabledButton = $event.detail.params"
                        class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 transform disabled:bg-gray-400 hover:scale-[1.02] active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="mr-2 lucide lucide-send-icon lucide-send">
                            <path
                                d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z" />
                            <path d="m21.854 2.147-10.94 10.939" />
                        </svg>
                        <span class="font-semibold tracking-wider text-white dark:text-gray-800">Kirim</span>
                    </button>

                </form>

                <div class="space-y-6 text-center" x-show="isSend" x-transition.duration.500ms x-transition.opacity>
                    <div class="flex items-center justify-center">
                        <div class="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="text-center lucide lucide-circle-check-big-icon lucide-circle-check-big text-emerald-400">
                                <path d="M21.801 10A10 10 0 1 1 17 3.335" />
                                <path d="m9 11 3 3L22 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-lg font-semibold text-gray-600 dark:text-white">Keluhan anda sudah kami terima
                        </p>
                        <p class="text-lg font-semibold text-gray-600 dark:text-white">Tim kami akan menghubungi anda
                        </p>
                    </div>

                    <div
                        class="flex items-center py-3 text-xs text-gray-400 uppercase before:flex-1 before:border-t before:border-gray-200 before:me-6 after:flex-1 after:border-t after:border-gray-200 after:ms-6 dark:text-neutral-500 dark:before:border-neutral-600 dark:after:border-neutral-600">
                        atau
                    </div>

                    <div class="">
                        <a href="/bantuan" wire:navigate class="font-semibold text-emerald-500">
                            Ajukan keluhan baru
                        </a>
                    </div>
                </div>
            </div>

            {{-- <!-- Footer --> --}}
            {{-- <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Butuh bantuan lebih lanjut?
                    <a href="#"
                        class="font-medium transition-colors duration-200 text-emerald-500 hover:text-emerald-600">
                        Hubungi kami
                    </a>
                </p>
            </div> --}}
        </div>

    </div>
    @livewireRecaptcha
</div>