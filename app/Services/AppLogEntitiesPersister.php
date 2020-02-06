<?php

namespace App\Services;

use App\ApplicationLog;
use App\ApplicationLogSection;
use App\DetectionsInfoBlock;
use App\GenericDataInfoBlock;
use App\ProductUseInfoBlock;
use App\ZoneAnalysisInfoBlock;
use ArrayIterator;
use Illuminate\Support\Arr;
use MultipleIterator;
use Net7\Documents\Document;
use function array_merge;
use function throw_if;

class AppLogEntitiesPersister
{
    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $zone_analysis_info_blocks_data
     * @throws \Exception
     */
    protected function persistZoneAnalysisInfoBlocks(ApplicationLogSection &$app_log_section, $zone_analysis_info_blocks_data = [])
    {
        if (!empty($zone_analysis_info_blocks_data)) {
            foreach ($zone_analysis_info_blocks_data as $zone_analysis_info_block_data) {
                $id = $zone_analysis_info_block_data['id'];
                $attributes = $zone_analysis_info_block_data['attributes'];
                // Se percentage_in_work è null significa che la zona in questione dev'essere:
                // - rimossa: se è presente l'id
                // - ignorata: se NON è presente l'id
                if (!$attributes['percentage_in_work']) {
                    if ($id) {
                        $zone_analysis_info_block = $app_log_section->zone_analysis_info_blocks()->find($id);
                        if ($zone_analysis_info_block) {
                            $zone_analysis_info_block->delete();
                        }
                    }
                    continue;
                }

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
     * @throws \Throwable
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

                $doc_ids = $this->persistImages($detections_info_block, $attributes['photos']);
                // valido solo per i detection_blocks: metto in correlazione immagini e detections attraverso i doc_id
                if (!empty($doc_ids)) {
                    $detections = $detections_info_block->detections;
                    if (!empty($detections)) {
                        $new_detections = [];
                        foreach ($detections as $detection) {
                            if (isset($detection['image_doc_id']) && $detection['image_doc_id']) {
                                $key_id = $detection['image_doc_id'];
                                if (Arr::has($doc_ids, $key_id)) {
                                    $detection['image_doc_id'] = $doc_ids[$key_id];
                                }
                            }
                            $new_detections[] = $detection;
                        }
                        if (!empty($new_detections)) {
                            $detections_info_block->update([
                                'detections' => $new_detections
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $generic_data_info_blocks_data
     * @throws \Throwable
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

                $this->persistImages($generic_data_info_block, $attributes['photos']);
            }
        }
    }

    /**
     * @param GenericDataInfoBlock|DetectionsInfoBlock $block
     * @param $photos_data
     * @return mixed
     * @throws \Throwable
     */
    protected function addImage(&$block, $photos_data)
    {
        throw_if(!isset($photos_data['doc_type']) || !isset($photos_data['base64']), 'Exception', 'Image data are not correct.');

        $type = $photos_data['doc_type'];
        $base64File = $photos_data['base64'];
        $filename = Arr::get($photos_data, 'filename', 'block_' . $type . '.jpg');
        $file = Document::createUploadedFileFromBase64($base64File, $filename);
        /** @var Document $doc */
        $doc = $block->addDocumentFileDirectly($file, $filename, $type);
        if ($doc) {
            return $doc->id;
        }
    }

    /**
     * @param GenericDataInfoBlock|DetectionsInfoBlock $block
     * @param $images_data
     * @param $docs_ids
     * @throws \Throwable
     */
    protected function extractImagesDataFromArrayAndSaveDocIds(&$block, $images_data, &$docs_ids)
    {
        if (!empty($images_data)) {
            foreach ($images_data as $image_data) {
                throw_if(!isset($image_data['id']), 'Exception', 'Cannot upload an image without ID.');
                // Cerco Document con questo ID e aggiorno l'immagine se non ce l'ho
                $proposed_id = $image_data['id'];
                $doc = Document::find($proposed_id);
                if ($doc) {
                    $docs_ids[$doc->id] = $doc->id;
                } else {
                    $doc_id = $this->addImage($block, $image_data['attributes']);
                    if ($doc_id) {
                        $docs_ids[$proposed_id] = $doc_id;
                    }
                }
            }
        }
    }

    /**
     * @param GenericDataInfoBlock|DetectionsInfoBlock $block
     * @param $images_data
     * @throws \Throwable
     */
    protected function removeDocuments(&$block, $images_data)
    {
        if (!empty($images_data)) {
            foreach ($images_data as $image_data) {
                throw_if(!isset($image_data['id']), 'Exception', 'Cannot upload an image without ID.');
                // Cerco Document con questo ID e aggiorno l'immagine se non ce l'ho
                $doc = Document::find($image_data['id']);
                if ($doc) {
                    $block->deleteDocument($doc);
                }
            }
        }
    }

    /**
     * @param GenericDataInfoBlock|DetectionsInfoBlock $block
     * @param $photos_data
     * @return array
     * @throws \Throwable
     */
    protected function persistImages(&$block, $photos_data)
    {
        if (!empty($photos_data)) {
            throw_if(!isset($photos_data['data']), 'Exception', 'Photos array needs "data" section.');
            $data = $photos_data['data'];
            if (!empty($data)) {
                $docs_ids = [];
                if (isset($data['detailed_images'])) {
                    $this->extractImagesDataFromArrayAndSaveDocIds($block, $data['detailed_images'], $docs_ids);
                }
                if (isset($data['additional_images'])) {
                    $this->extractImagesDataFromArrayAndSaveDocIds($block, $data['additional_images'], $docs_ids);
                }
                if (isset($data['deleted_images'])) {
                    $this->removeDocuments($block, $data['deleted_images']);
                }
                return $docs_ids;
            }
        }
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     * @throws \Exception
     */
    protected function persistZonesSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $this->persistZoneAnalysisInfoBlocks($app_log_section, $attributes['zone_analysis_info_blocks']);
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     * @throws \Throwable
     */
    protected function persistPreparationSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistProductUseInfoBlocks($app_log_section, $attributes['product_use_info_blocks']);
        $this->persistDetectionsInfoBlock($app_log_section, $attributes['detections_info_blocks']);
        $this->persistGenericDataInfoBlock($app_log_section, $attributes['generic_data_info_blocks']);
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     * @throws \Throwable
     */
    protected function persistApplicationSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistProductUseInfoBlocks($app_log_section, $attributes['product_use_info_blocks']);
        $this->persistDetectionsInfoBlock($app_log_section, $attributes['detections_info_blocks']);
        $this->persistGenericDataInfoBlock($app_log_section, $attributes['generic_data_info_blocks']);
    }

    /**
     * @param ApplicationLogSection $app_log_section
     * @param array $attributes
     * @throws \Throwable
     */
    protected function persistInspectionSection(ApplicationLogSection &$app_log_section, $attributes = [])
    {
        $app_log_section->update([
            'date_hour' => isset($attributes['date_hour']) ? $attributes['date_hour'] : null,
        ]);
        $this->persistDetectionsInfoBlock($app_log_section, $attributes['detections_info_blocks']);
    }

    /**
     * @param ApplicationLog $app_log
     * @param array $section_data
     * @return bool
     * @throws \Throwable
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
                'application_log_id' => $app_log->id,
                'is_started' => $attributes['is_started']
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
