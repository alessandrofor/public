<?php

namespace App\Controller;

class baseArrayLivTours
{
    public static function getPegs(): array
    {
        return ['pegA', 'pegB', 'pegC'];;
    }
    private static function getDisks(): array
    {
        return ['disk1', 'disk2', 'disk3', 'disk4', 'disk5', 'disk6', 'disk7'];
    }

    public static function getPazzle(): array
    {
        $pegs = array_fill_keys(self::getPegs(), null);
        // si può dare qualsiasi nome ai dischi, ma fai attenzione qui
        $pegs[array_values(self::getPegs())[0]] = self::getDisks();

        return $pegs;
    }

    public static function diskIsSmaller(string $from, string $to): ?bool
    {
        $disks = array_values(self::getDisks());
        $positionFrom = array_search($from, $disks);
        $positionTo = array_search($to, $disks);
        if ($positionFrom === false || $positionTo === false) return null;
        // si può dare qualsiasi nome ai dischi, ma fai attenzione qui
        return $positionFrom > $positionTo;
    }
}