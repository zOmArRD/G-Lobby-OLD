<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\network\skin;

use Exception;
use JsonException;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\SkinAdapter;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use RuntimeException;

final class MojangAdapter implements SkinAdapter
{
    private array $personaSkins = [];

    /**
     * @throws JsonException
     */
    public function toSkinData(Skin $skin): SkinData
    {
        if (isset($this->personaSkins[$skin->getSkinId()])) {
            return $this->personaSkins[$skin->getSkinId()];
        }
        $capeData = $skin->getCapeData();
        $capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
        $geometryName = $skin->getGeometryName();
        if ($geometryName === "") {
            $geometryName = "geometry.humanoid.custom";
        }
        $resourcePatch = json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR);
        if ($resourcePatch === false) {
            throw new RuntimeException("json_encode() failed: " . json_last_error_msg());
        }
        return new SkinData($skin->getSkinId(), "", //TODO: playfab ID
            $resourcePatch, SkinImage::fromLegacy($skin->getSkinData()), [], $capeImage, $skin->getGeometryData());
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function fromSkinData(SkinData $data): Skin
    {
        $capeData = $data->getCapeImage()->getData();

        if ($data->isPersona()) {
            $this->personaSkins[$data->getSkinId()] = $data;
            return new Skin($data->getSkinId(), str_repeat(random_bytes(3) . "\xff", 2048), $capeData);
        }

        $resourcePatch = json_decode($data->getResourcePatch(), true, 512, JSON_THROW_ON_ERROR);
        if (is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
            $geometryName = $resourcePatch["geometry"]["default"];
        } else {
            throw new InvalidSkinException("Missing geometry name field");
        }

        return new Skin($data->getSkinId(), $data->getSkinImage()->getData(), $capeData, $geometryName, $data->getGeometryData());
    }
}