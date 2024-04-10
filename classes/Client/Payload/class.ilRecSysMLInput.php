<?php

class MLInput {
    public int $usrId;
    public int $crsId;
    public int $timestamp;
    public array $sectionIds;
    public array $materialTypes;
    public array $encoderTypes;

    public function __construct(int $usrId, int $crsId, int $timestamp, array $sectionIds, array $materialTypes, array $encoderTypes) {
        $this->usrId = $usrId;
        $this->crsId = $crsId;
        $this->timestamp = $timestamp;
        $this->sectionIds = $sectionIds;
        $this->materialTypes = $materialTypes;
        $this->encoderTypes = $encoderTypes;
    }
}

?>