<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prodi;
use App\Models\Position;

class ApiController extends Controller
{
    /**
     * Get prodi by jurusan ID
     */
    public function getProdisByJurusan($jurusanId)
    {
        try {
            $prodis = Prodi::where('jurusan_id', $jurusanId)
                ->orderBy('nama_prodi')
                ->get(['id', 'nama_prodi', 'jenjang']);
            
            return response()->json($prodis);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat data program studi'], 500);
        }
    }
    
    /**
     * Get all positions (positions are not tied to specific units)
     */
    public function getPositionsByUnit($unitId)
    {
        try {
            // Positions are not tied to specific units, so return all positions
            $positions = Position::orderBy('nama')
                ->get(['id', 'nama']);
            
            return response()->json($positions);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat data posisi'], 500);
        }
    }
}
