<?php
namespace App\Entity;
use App\Entity\Versus;
class BinaryVersus extends Versus {
    private array $previous;

    public function __construct() {}

    public function getPrevious(){
        return $this->previous;
    }

    public function setPrevious($previous){
        $this->previous = $previous;
    }
    
    

}