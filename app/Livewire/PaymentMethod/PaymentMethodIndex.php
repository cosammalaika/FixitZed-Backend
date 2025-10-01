<?php

namespace App\Livewire\PaymentMethod;

use App\Models\PaymentMethod;
use Livewire\Component;

class PaymentMethodIndex extends Component
{
    public $name = '';
    public $code = '';
    public $sort_order = 0;

    protected $rules = [
        'name' => 'required|string|max:100',
        'code' => 'required|string|max:50|unique:payment_methods,code',
        'sort_order' => 'nullable|integer',
    ];

    public function add()
    {
        $data = $this->validate();
        PaymentMethod::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'sort_order' => $data['sort_order'] ?? 0,
            'active' => true,
        ]);
        $this->reset(['name', 'code', 'sort_order']);
        session()->flash('success', 'Payment method added');
    }

    public function toggle($id)
    {
        if ($m = PaymentMethod::find($id)) {
            $m->active = ! $m->active;
            $m->save();
        }
    }

    public function delete($id)
    {
        PaymentMethod::where('id', $id)->delete();
    }

    public function render()
    {
        return view('livewire.payment-method.payment-method-index', [
            'items' => PaymentMethod::orderBy('sort_order')->get(),
        ]);
    }
}

