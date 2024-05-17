<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateMeta extends Model
{
    protected $table = 'states_meta';


    public function states(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(State::class, 'metadata_id', 'metadata_id');
    }

    protected static $metadataCache = [];
    public static function getMetadata($deviceName)
    {
        if ($deviceName == null) {
            $deviceName = AppSettings::$natwaveDevices[0];
        }
        if (isset(self::$metadataCache[$deviceName])) {
            return self::$metadataCache[$deviceName];
        }
        $sensors = AppSettings::$sensors;
        // Required for converting entity_id to attributes_id
        $entityIds = [];
        // e.g sensor.natwave_ec
        foreach ($sensors as $sensor) {
            $entityIds[] = "sensor.{$deviceName}_{$sensor}";
        }

        // Required for querying states table
        $metadataToEntityIds = [];
        //$metadatas = StateMeta::whereIn('entity_id', $entityIds)->get()->toArray();
        $metadataIds = [];
        foreach ($entityIds as $entityId) {
            $metadata = StateMeta::where('entity_id', $entityId)->first();
            if (!$metadata) continue;
            $metadataToEntityIds[$metadata['metadata_id']] = $metadata['entity_id'];
            $metadataIds[] = $metadata['metadata_id'];
        }
        return self::$metadataCache[$deviceName] = [
            'metadataToEntityIds' => $metadataToEntityIds,
            'metadataIds' => $metadataIds
        ];
    }
}
