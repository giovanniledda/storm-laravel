<?php

namespace App\Services;

use App\ApplicationLog;
use App\ApplicationLogSection;
use App\Boat;
use App\DetectionsInfoBlock;
use App\GenericDataInfoBlock;
use App\ProductUseInfoBlock;
use App\Project;
use App\Task;
use App\ZoneAnalysisInfoBlock;
use function array_merge;

class AppLogEntitiesPersister
{
    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $zone_analysis_info_blocks_data
     */
    protected function persistZoneAnalysisInfoBlocks(ApplicationLogSection &$app_log_section, $zone_analysis_info_blocks_data = [])
    {
        if (!empty($zone_analysis_info_blocks_data)) {
            foreach ($zone_analysis_info_blocks_data as $zone_analysis_info_block_data) {
                $id = $zone_analysis_info_block_data['id'];
                $attributes = $zone_analysis_info_block_data['attributes'];
                /** @var ZoneAnalysisInfoBlock $zone_analysis_info_block */
                $zone_analysis_info_block = $app_log_section->zone_analysis_info_blocks()->find($id);
                if (!$zone_analysis_info_block) {
                    $attributes = array_merge($attributes, ['application_log_section_id' => $app_log_section->id]);
                    $zone_analysis_info_block = ZoneAnalysisInfoBlock::create($attributes);
                } else {
                    $zone_analysis_info_block->update($attributes);
                }
            }
        }
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $product_use_info_blocks_data
     */
    protected function persistProductUseInfoBlocks(ApplicationLogSection &$app_log_section, $product_use_info_blocks_data = [])
    {
        if (!empty($product_use_info_blocks_data)) {
            foreach ($product_use_info_blocks_data as $product_use_info_block_data) {
                $id = $product_use_info_block_data['id'];
                $attributes = $product_use_info_block_data['attributes'];
                /** @var ProductUseInfoBlock $product_use_info_block */
                $product_use_info_block = $app_log_section->product_use_info_blocks()->find($id);
                if (!$product_use_info_block) {
                    $attributes = array_merge($attributes, ['application_log_section_id' => $app_log_section->id]);
                    $product_use_info_block = ProductUseInfoBlock::create($attributes);
                } else {
                    $product_use_info_block->update($attributes);
                }
            }
        }
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $detections_info_blocks_data
     */
    protected function persistDetectionsInfoBlock(ApplicationLogSection &$app_log_section, $detections_info_blocks_data = [])
    {
        if (!empty($detections_info_blocks_data)) {
            foreach ($detections_info_blocks_data as $detections_info_block_data) {
                $id = $detections_info_block_data['id'];
                $attributes = $detections_info_block_data['attributes'];
                /** @var DetectionsInfoBlock $detections_info_block */
                $detections_info_block = $app_log_section->detections_info_blocks()->find($id);
                if (!$detections_info_block) {
                    $attributes = array_merge($attributes, ['application_log_section_id' => $app_log_section->id]);
                    $detections_info_block = DetectionsInfoBlock::create($attributes);
                } else {
                    $detections_info_block->update($attributes);
                }
            }
        }
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $generic_data_info_blocks_data
     */
    protected function persistGenericDataInfoBlock(ApplicationLogSection &$app_log_section, $generic_data_info_blocks_data = [])
    {
        if (!empty($generic_data_info_blocks_data)) {
            foreach ($generic_data_info_blocks_data as $generic_data_info_block_data) {
                $id = $generic_data_info_block_data['id'];
                $attributes = $generic_data_info_block_data['attributes'];
                /** @var GenericDataInfoBlock $generic_data_info_block */
                $generic_data_info_block = $app_log_section->generic_data_info_blocks()->find($id);
                if (!$generic_data_info_block) {
                    $attributes = array_merge($attributes, ['application_log_section_id' => $app_log_section->id]);
                    $generic_data_info_block = GenericDataInfoBlock::create($attributes);
                } else {
                    $generic_data_info_block->update($attributes);
                }
            }
        }
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     */
    protected function persistZonesSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'is_started' => 1,
//            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistZoneAnalysisInfoBlocks($app_log_section, $attributes['zone_analysis_info_blocks']);
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     */
    protected function persistPreparationSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'is_started' => 1,
            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistProductUseInfoBlocks($app_log_section, $attributes['product_use_info_blocks']);
        $this->persistDetectionsInfoBlock($app_log_section, $attributes['detections_info_blocks']);
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     */
    protected function persistApplicationSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'is_started' => 1,
            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistProductUseInfoBlocks($app_log_section, $attributes['product_use_info_blocks']);
        $this->persistDetectionsInfoBlock($app_log_section, $attributes['detections_info_blocks']);
        $this->persistGenericDataInfoBlock($app_log_section, $attributes['generic_data_info_blocks']);
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     */
    protected function persistInspectionSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'is_started' => 1,
            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistDetectionsInfoBlock($app_log_section, $attributes['detections_info_blocks']);
    }

    /**
     * @param ApplicationLog $app_log
     * @param array $section_data
     * @return bool
     */
    public function persistSection(ApplicationLog $app_log, $section_data = [])
    {
        $attributes = $section_data['attributes'];
        $id = $section_data['id'];
        $type = $attributes['section_type'];
        /** @var ApplicationLogSection $app_log_section */
        $app_log_section = $app_log->application_log_sections()->find($id);
        if (!$app_log_section) {
            $app_log_section = ApplicationLogSection::create([
                'section_type' => $type,
                'application_log_id' => $app_log->id
            ]);
        } else if ($app_log_section->updated_at == $attributes['updated_at']) {
            return false;
        }

        switch ($type) {
            case APPLICATION_LOG_SECTION_TYPE_ZONES:
                $this->persistZonesSection($app_log_section, $attributes);
                break;
            case APPLICATION_LOG_SECTION_TYPE_PREPARATION:
                $this->persistPreparationSection($app_log_section, $attributes);
                break;
            case APPLICATION_LOG_SECTION_TYPE_APPLICATION:
                $this->persistApplicationSection($app_log_section, $attributes);
                break;
            case APPLICATION_LOG_SECTION_TYPE_INSPECTION:
                $this->persistInspectionSection($app_log_section, $attributes);
                break;
        }
    }
}
