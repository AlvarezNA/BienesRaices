<?php 
namespace App;
class ActiveRecord {
    //base de datos
    protected static $db;
    protected static $columnasDB = [];
    protected static $tabla = '';
    //errores

    protected static $errores = []; 

   

        //definir la conexion a la base de datos
        public static function setDB($database) {
            self::$db = $database;
        }



    public function guardar(){
        if(!is_null($this->id)){  
            //actualizar
            $this->actualizar();
  } else {
    $this->crear();
  }
}
public function crear() {
   // echo "guardando en la base de datos"; 
   $atributos = $this->sanitizarAtributos();


    $query  = " INSERT INTO " . static::$tabla . " ( ";
    $query .= join(', ', array_keys($atributos));
    $query .= " ) VALUES (' "; 
    $query .=  join("', '", array_values($atributos));
    $query .= " ') ";
   
    $resultado = self::$db->query($query);
   //mensaje de exito
    if($resultado) {
        //redireccionando al usuario
        header('Location: /admin?resultado=1');
       }

}

public function actualizar() {
    $atributos = $this->sanitizarAtributos();

    $valores = [];
    foreach($atributos as $key => $value) {
        $valores[] = "{$key}='{$value}'"; 
    }
    
    $query = " UPDATE " . static::$tabla . " SET "; 
    $query .= join(', ', $valores);
    $query .= " WHERE id = '" . self::$db->escape_string($this->id) ."' ";
    $query .= "LIMIT 1 ";

    $resultado = self::$db->query($query);
    if($resultado) {
        //Redireccionar al usuario 
    header('Location: /admin?resultado=2');
}
}
//eliminar un registro
public function eliminar() {
    // Eliminar registros relacionados en la tabla 'propiedades'
    $query_propiedades = "DELETE FROM propiedades WHERE vendedores_id = " . $this->id;
    $resultado_propiedades = self::$db->query($query_propiedades);

    // Verificar si se eliminaron correctamente los registros de 'propiedades'
    if ($resultado_propiedades) {
        // Proceder a eliminar el vendedor
        $query_vendedor = "DELETE FROM " . static::$tabla . " WHERE id = " . $this->id . " LIMIT 1";
        $resultado_vendedor = self::$db->query($query_vendedor);

        if ($resultado_vendedor) {
            $this->borrarImagen();
            header('Location: /admin?resultado=3');
        } else {
            // Error al eliminar el vendedor
            // Manejar el error según sea necesario
        }
    } else {
        //Error al eliminar los registros de 'propiedades'
        // Manejar el error según sea necesario
    }
}


//identificar y unir los atributos de la Bd
public function atributos() {
    $atributos = [];
    foreach(static::$columnasDB as $columna) {
        if($columna === 'id') continue; 
        $atributos[$columna] = $this->$columna;
    }
    return $atributos;
}
public function sanitizarAtributos() {
    $atributos = $this->atributos();
    $sanitizado = [];
    foreach($atributos as $key => $value ){
        $sanitizado[$key] = self::$db->escape_string($value);
  
    }
    return $sanitizado;
}

//subida de archivos

Public function setImagen($imagen) {
    //ELIMINA LA IMAGEN PREVIA
    if(!is_null($this->id)){
        $this->borrarImagen();
    
    }
    //asignar al atributo el nombre de la imagen
    if($imagen) {
        $this->imagen = $imagen;
    }
}
//Eliminar archivos
public function borrarImagen() {
    $existeArchivo = file_exists(CARPETA_IMAGENES . $this->imagen);
    if($existeArchivo){
        unlink(CARPETA_IMAGENES . $this->imagen);
    }
}

//validacion
public static function getErrores() {
    return static::$errores;
}

public function validar() {
        static::$errores = [];
        return static::$errores;
   
}
//Lista todas los registros
    public static function all() {
 $query = " SELECT * FROM " . static::$tabla;
 

   $resultado = self::consultarSQL($query); 
 
   return $resultado;
}


public static function get($cantidad) {
    $query = " SELECT * FROM " . static::$tabla . " LIMIT " . $cantidad;
    
   
      $resultado = self::consultarSQL($query); 
    
      return $resultado;
   }

//buscar un registro por su id
    public static function find($id) {
        $query = " SELECT * FROM " . static::$tabla .  " WHERE id = ${id}";
        $resultado = self::consultarSQL($query);
        
        return array_shift( $resultado);
    }
    public static function consultarSQL ($query) {
        //consultar la base de datos
        $resultado = self::$db->query($query);
        
        
        //iterar los resultados
        $array = [];
        while($registro = $resultado->fetch_assoc()) {
            $array[] = static::crearObjeto($registro);
        }
        //liberar la memoria
        $resultado->free();
        //retornar los resultado
        return $array;
    }
protected static function crearObjeto ($registro) {
    $objeto = new static;
    foreach($registro as $key => $value) {
        if(property_exists($objeto, $key )) {
            $objeto->$key = $value;
        }
    }
    return $objeto;
}

    //sincroniza el objeto en memoria, con los cambios realizados por el usuario
    public function sincronizar($args = [])
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->{$key} = $value;
            }
        }
    }






  
    
}