<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @copyright Copyright 2019 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Class implmenting metadata output for the oai_qdc metadata format.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_OaiQdc implements OaiPmhRepository_Metadata_FormatInterface
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'oai_qdc';

    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://worldcat.org/xmlschemas/qdc-1.0/';

    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://worldcat.org/xmlschemas/qdc/1.0/qdc-1.0.xsd';
    const QDC_SCHEMA = 'http://dublincore.org/schemas/xmls/qdc/2008/02/11/qualifieddc.xsd';

    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';
    const DCTERMS_NAMESPACE_URI = 'http://purl.org/dc/terms/';

    /**
     * Appends Dublin Core metadata.
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata($item, $metadataElement)
    {
        $document = $metadataElement->ownerDocument;
        $oai_qdc = $document->createElementNS(
            self::METADATA_NAMESPACE, 'oai_qdc:qualifieddc');
        $metadataElement->appendChild($oai_qdc);

        $oai_qdc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai_qdc->setAttribute('xmlns:dcterms', self::DCTERMS_NAMESPACE_URI);
        $oai_qdc->declareSchemaLocation(self::METADATA_NAMESPACE, self::METADATA_SCHEMA);

        $dcExtendedElements = array(
            'Title' => 'dc:title',
            'Creator' => 'dc:creator',
            'Subject' => 'dc:subject',
            'Description' => 'dc:description',
            'Publisher' => 'dc:publisher',
            'Contributor' => 'dc:contributor',
            'Date' => 'dc:date',
            'Type' => 'dc:type',
            'Format' => 'dc:format',
            'Identifier' => 'dc:identifier',
            'Source' => 'dc:source',
            'Language' => 'dc:language',
            'Relation' => 'dc:relation',
            'Coverage' => 'dc:coverage',
            'Rights' => 'dc:rights',
            'Abstract' => 'dcterms:abstract',
            'Access Rights' => 'dcterms:accessRights',
            'Accrual Method' => 'dcterms:accrualMethod',
            'Accrual Periodicity' => 'dcterms:accrualPeriodicity',
            'Accrual Policy' => 'dcterms:accrualPolicy',
            'Alternative Title' => 'dcterms:alternative',
            'Audience' => 'dcterms:audience',
            'Date Available' => 'dcterms:available',
            'Bibliographic Citation' => 'dcterms:bibliographicCitation',
            'Conforms To' => 'dcterms:conformsTo',
            'Date Created' => 'dcterms:created',
            'Date Accepted' => 'dcterms:dateAccepted',
            'Date Copyrighted' => 'dcterms:dateCopyrighted',
            'Date Submitted' => 'dcterms:dateSubmitted',
            'Audience Education Level' => 'dcterms:educationLevel',
            'Extent' => 'dcterms:extent',
            'Has Format' => 'dcterms:hasFormat',
            'Has Part' => 'dcterms:hasPart',
            'Has Version' => 'dcterms:hasVersion',
            'Instructional Method' => 'dcterms:instructionalMethod',
            'Is Format Of' => 'dcterms:isFormatOf',
            'Is Part Of' => 'dcterms:isPartOf',
            'Is Referenced By' => 'dcterms:isReferencedBy',
            'Is Replaced By' => 'dcterms:isReplacedBy',
            'Is Required By' => 'dcterms:isRequiredBy',
            'Date Issued' => 'dcterms:issued',
            'Is Version Of' => 'dcterms:isVersionOf',
            'License' => 'dcterms:license',
            'Mediator' => 'dcterms:mediator',
            'Medium' => 'dcterms:medium',
            'Date Modified' => 'dcterms:modified',
            'Provenance' => 'dcterms:provenance',
            'References' => 'dcterms:references',
            'Replaces' => 'dcterms:replaces',
            'Requires' => 'dcterms:requires',
            'Rights Holder' => 'dcterms:rightsHolder',
            'Spatial Coverage' => 'dcterms:spatial',
            'Table Of Contents' => 'dcterms:tableOfContents',
            'Temporal Coverage' => 'dcterms:temporal',
            'Date Valid' => 'dcterms:valid'
        );

        foreach ($dcExtendedElements as $elementName => $propertyName) {
            try {
                $texts = $item->getElementTexts('Dublin Core', $elementName);
            } catch (Omeka_Record_Exception $e) {
                continue;
            }

            // Prepend the item type, if any.
            if ($elementName == 'Type' && get_option('oaipmh_repository_expose_item_type')) {
                if ($dcType = $item->getProperty('item_type_name')) {
                    $oai_qdc->appendNewElement('dc:type', $dcType);
                }
            }

            foreach ($texts as $text) {
                $oai_qdc->appendNewElement($propertyName, $text->text);
            }

            // Append the browse URI to all results
            if ($propertyName == 'dc:identifier') {
                $oai_qdc->appendNewElement('dc:identifier', record_url($item, 'show', true));

                // Also append an identifier for each file
                if(get_option('oaipmh_repository_expose_files')) {
                    $files = $item->getFiles();
                    foreach ($files as $file) {
                        $oai_qdc->appendNewElement('dc:identifier', $file->getWebPath('original'));
                    }
                }
            }
        }
    }
}
