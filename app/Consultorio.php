php <?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $fillable = ['nombre','direccion','telefono','foto','activo','config'];

    /**
     * Decodificar la config JSON automáticamente.
     */
    public function getConfigAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        return json_decode($value, true);
    }

    /**
     * Codificar la config como JSON al guardar.
     */
    public function setConfigAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['config'] = json_encode($value);
        } else {
            $this->attributes['config'] = $value;
        }
    }
}
