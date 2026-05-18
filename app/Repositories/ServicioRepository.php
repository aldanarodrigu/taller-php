<?php

namespace App\Repositories;

use App\Models\Servicio;

class ServicioRepository
{
    public function create(array $data): Servicio
    {
        return Servicio::create($data);
    }

    public function findById(int $id): ?Servicio
    {
        return Servicio::with('profesional.user')->find($id);
    }

    public function update(Servicio $servicio, array $data): Servicio
    {
        $servicio->update($data);
        return $servicio->fresh();
    }

    public function delete(Servicio $servicio): void
    {
        $servicio->delete();
    }

    public function findByProfesional(int $profesionalId)
    {
        return Servicio::where('profesional_id', $profesionalId)->get();
    }

    public function listarConFiltros(array $filtros)
    {
        $query = Servicio::with('profesional.user')->activo();

        if (!empty($filtros['tipo'])) {
            $query->where('tipo', $filtros['tipo']);
        }

        if (!empty($filtros['modalidad'])) {
            $query->where('modalidad', $filtros['modalidad']);
        }

        if (!empty($filtros['precio_max'])) {
            $query->where('precio', '<=', $filtros['precio_max']);
        }

        if (!empty($filtros['precio_min'])) {
            $query->where('precio', '>=', $filtros['precio_min']);
        }

        if (!empty($filtros['nombre'])) {
            $query->where('nombre', 'like', '%' . $filtros['nombre'] . '%');
        }

        // Filtro por proximidad geográfica usando fórmula de Haversine
        if (!empty($filtros['lat']) && !empty($filtros['lng']) && !empty($filtros['radio_km'])) {
            $lat   = $filtros['lat'];
            $lng   = $filtros['lng'];
            $radio = $filtros['radio_km'];

            $query->selectRaw("*, 
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitud)) *
                    cos(radians(longitud) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitud))
                )) AS distancia_km", [$lat, $lng, $lat])
                ->having('distancia_km', '<=', $radio)
                ->orderBy('distancia_km');
        }

        return $query->paginate(15);
    }
}