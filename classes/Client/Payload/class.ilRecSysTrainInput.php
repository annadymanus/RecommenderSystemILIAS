<?php

class TrainInput {
    public int $crsId;
    public array $encoderTypes;

    public function __construct(int $crsId, array $encoderTypes) {
        $this->crsId = $crsId;
        $this->encoderTypes = $encoderTypes;
    }
}

?>