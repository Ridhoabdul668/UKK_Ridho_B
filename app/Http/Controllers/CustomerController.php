<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('created_at', 'desc')->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        // Generate kode member otomatis
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->kode_member, 1);
            $newNumber = $lastNumber + 1;
            $kodeMember = 'M'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $kodeMember = 'M0001';
        }

        return view('customers.create', compact('kodeMember'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_member' => 'required|unique:customers',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'nullable|string',
            'jenis_diskon' => 'required|in:persen,nominal',
            'nilai_diskon' => 'required|integer|min:0',
        ]);

        $customer = Customer::create([
            'kode_member' => $request->kode_member,
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'poin' => 0,
            'jenis_diskon' => $request->jenis_diskon,
            'nilai_diskon' => $request->nilai_diskon,
        ]);

        Log::catat(
            Auth::id(),
            'create_member',
            'customers',
            $customer->id,
            'Menambah member baru: '.$customer->nama.' ('.$customer->kode_member.')'
        );

        return redirect()->route('customers.index')->with('success', 'Member berhasil ditambahkan');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'kode_member' => 'required|unique:customers,kode_member,'.$customer->id,
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,'.$customer->id,
            'no_hp' => 'required|string|max:15',
            'alamat' => 'nullable|string',
            'jenis_diskon' => 'required|in:persen,nominal',
            'nilai_diskon' => 'required|integer|min:0',
        ]);

        $oldData = $customer->toArray();
        $customer->update($request->all());

        Log::catat(
            Auth::id(),
            'update_member',
            'customers',
            $customer->id,
            'Mengupdate member: '.$customer->nama,
            $oldData,
            $customer->toArray()
        );

        return redirect()->route('customers.index')->with('success', 'Member berhasil diupdate');
    }

    public function destroy(Customer $customer)
    {
        $nama = $customer->nama;
        $kode = $customer->kode_member;
        $customer->delete();

        Log::catat(
            Auth::id(),
            'delete_member',
            'customers',
            $customer->id,
            'Menghapus member: '.$nama.' ('.$kode.')'
        );

        return redirect()->route('customers.index')->with('success', 'Member berhasil dihapus');
    }
}
