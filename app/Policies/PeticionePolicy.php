<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Peticione;

class PeticionePolicy
{
    /*Cumplimenta el código en el controlador y el archivo de políticas y pruébalos correctamente:
Sólo los usuarios con role_id=1, que significará administrador, podrán:
Actualizar una petición
Borrar una petición
Cambiar el estado
Además un usuario con role_id=2 y que haya subido una petición concreta, también
podrá realizar estas operaciones sobre ella:
Actualizar una petición
Borrar una petición
Por último, un usuario con role_id=2 también podrá firmar una petición una sola vez.
En este caso, puede ser una que él haya subido o no.*/


    /*public function before(User $user, string $ability) //Esta función deja pasar a todo para los usuarios con role_id == 1 ??????
    {
        if ($user->role_id == 1) {
            return true;
        }
    }*/


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Peticione $peticione): bool
    {
        return true;
    }
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }
    /**
     * Determine whether the user can update the model.
     */
    //1 admin, 2 usuario logueado
    public function update(User $user, Peticione $peticione): bool
    {
        if ($user->role_id == 1 || $peticione->user_id == $user->id) { //Si el usuario es admin o si su user->id coincide con el user_id de la petición.
            return true;
        }
        return false;
    }
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Peticione $peticione): bool
    {
        if ($user->role_id == 1 || $peticione->user_id == $user->id) { //Si el usuario es admin o si su user->id coincide con el user_id de la petición.
            return true;
        }
        return false;
    }
    public function cambiarEstado(User $user, Peticione $peticione): bool
    {
        return $user->role_id == 1;
    }

    public function firmar(User $user, Peticione $peticione): bool
    {
        $firmas = $peticione->firmas;
        foreach ($firmas as $firma) {
            if ($firma->id == $user->id) {
                return false;
            }
        }
        return true;
    }
}
