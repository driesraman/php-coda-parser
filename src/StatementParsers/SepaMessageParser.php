<?php

namespace Codelicious\Coda\StatementParsers;

use Codelicious\Coda\Lines\LineInterface;
use Codelicious\Coda\Lines\LineType;
use Codelicious\Coda\Values\StructuredSepaMessage;
use function Codelicious\Coda\Helpers\filterLinesOfTypes;

class SepaMessageParser
{
	const TAG_MAPPING = [
		'EREF' => 'end_to_end_reference',
		'PREF' => 'payment_reference',
		'MARF' => 'mandate_reference',
		'SREF' => 'structured_reference',
		'REMI' => 'remittance_info',
		'USTRD' => 'unstructured_remittance',
		'TYPE' => 'transaction_type',
		'ROC' => 'creditor_reference',
        'CODE' => 'country_code',
		'NBTR' => 'number_of_transactions',
		'IREF' => 'instruction_reference'
	];

	const PARTY_TYPES = [
        'BENM' => 'beneficiary',
        'ORDP' => 'ordering_party',
		'DEB' => 'debtor'
    ];

	const PARTY_FIELDS = [
        'NAME' => 'name',
        'ADDR' => 'address',
        'INFO' => 'account',
        'ID' => 'id'
    ];

    /**
     * Parse SEPA structured tags from InformationPart lines
     * Extracts all common SEPA payment tags for comprehensive transaction data
     *
     * @param LineInterface[] $lines
     * @return StructuredSepaMessage|null Structured SEPA message object or null if no SEPA data
     */
    public function parse(array $lines)
    {
        return $this->parseString(
            $this->concatenateInformationLines($lines)
        );
    }

    public function parseString(string $message)
    {
        if (!$this->hasSepaInformation($message)) {
            return null;
        }

        $tags = $this->splitByTag($message, array_keys(self::TAG_MAPPING), array_keys(self::PARTY_TYPES));
        $result = [
            'sepa_message' => $message
        ];

        foreach (self::TAG_MAPPING as $tagName => $resultKey) {
            if (isset($tags[$tagName])) {
                $result[$resultKey] = $tags[$tagName];
            }
        }

        if (isset($tags['CODE']) && preg_match('/^([A-Z]{2})\/(.*)$/', $tags['CODE'], $matches)) {
            $result['country_code'] = $matches[1];
            $result['additional_code'] = $matches[2];
        }

        foreach (self::PARTY_TYPES as $tagName => $resultKey) {
            if (!isset($tags[$tagName])) {
                continue;
            }

            $partyData = $this->splitByTag($tags[$tagName], array_keys(self::PARTY_FIELDS));

            $mappedParty = [];
            foreach (self::PARTY_FIELDS as $fieldName => $fieldKey) {
                if (!isset($partyData[$fieldName])) {
                    continue;
                }

                $value = $partyData[$fieldName];

                foreach (array_keys(self::PARTY_TYPES) as $partyTypeTag) {
                    $value = preg_replace('/\/\s*' . $partyTypeTag . '(\s|$)/', '', $value);
                }
                $value = trim($value);

                if ($fieldName === 'INFO' || $fieldName === 'ID') {
                    $valueNoSpaces = str_replace(' ', '', $value);
                    if (preg_match('/([A-Z]{2}[0-9A-Z]+)/', $valueNoSpaces, $matches)) {
                        $mappedParty['account'] = $matches[1];
                    }
                } else {
                    $mappedParty[$fieldKey] = $value;
                }

                if ($fieldName === 'ID') {
                    $mappedParty['id'] = $value;
                }
            }

            $result[$resultKey] = $mappedParty;
        }

        if (!empty($result['beneficiary']['name'])) {
            $result['name'] = $result['beneficiary']['name'];
        } elseif (!empty($result['ordering_party']['name'])) {
            $result['name'] = $result['ordering_party']['name'];
        } elseif (!empty($result['debtor']['name'])) {
            $result['name'] = $result['debtor']['name'];
        }

        if (!empty($result['beneficiary']['account'])) {
            $result['account'] = $result['beneficiary']['account'];
        } elseif (!empty($result['ordering_party']['account'])) {
            $result['account'] = $result['ordering_party']['account'];
        } elseif (!empty($result['debtor']['account'])) {
            $result['account'] = $result['debtor']['account'];
        }

        return new StructuredSepaMessage(
            $result['sepa_message'] ?? '',
            $result['end_to_end_reference'] ?? '',
            $result['payment_reference'] ?? '',
            $result['mandate_reference'] ?? '',
            $result['structured_reference'] ?? '',
            $result['instruction_reference'] ?? '',
            $result['remittance_info'] ?? '',
            $result['unstructured_remittance'] ?? '',
            $result['transaction_type'] ?? '',
            $result['creditor_reference'] ?? '',
            $result['country_code'] ?? '',
            $result['additional_code'] ?? '',
            $result['beneficiary'] ?? array(),
            $result['ordering_party'] ?? array(),
            $result['debtor'] ?? array(),
            $result['number_of_transactions'] ?? '',
            $result['name'] ?? '',
            $result['account'] ?? ''
        );
    }

    function splitByTag(string $string, ...$tagArrays): array
    {
        $allTags = array_merge(...$tagArrays);
        $isPartyFields = !empty($allTags) && empty(array_diff($allTags, array_keys(self::PARTY_FIELDS)));

        $quotedTags = array_map(
            function($tag) {
                $chars = str_split(preg_quote($tag, '/'));
                return implode('\s*', $chars);
            },
            $allTags
        );

        $pattern = $isPartyFields
            ? '/\/\/?(\s*(?:' . implode('|', $quotedTags) . ')\s*)\//'
            : '/\/(\s*(?:' . implode('|', $quotedTags) . ')\s*)\//';

        $parts = preg_split($pattern, $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $startIndex = 0;
        if (count($parts) > 0) {
            $normalizedFirst = str_replace(' ', '', trim($parts[0]));
            $firstIsTag = in_array($normalizedFirst, $allTags);
            $startIndex = $firstIsTag ? 0 : 1;
        }

        $mappedTags = [];
        for ($i = $startIndex; $i < count($parts); $i += 2) {
            if (isset($parts[$i]) && isset($parts[$i + 1])) {
                $tagName = str_replace(' ', '', trim($parts[$i]));
                $value = $parts[$i + 1];

                if (!$isPartyFields && isset($mappedTags[$tagName]) && array_key_exists($tagName, self::PARTY_TYPES)) {
                    $mappedTags[$tagName] .= '/' . $tagName . '/' . $value;
                } else {
                    $mappedTags[$tagName] = trim(rtrim($value, '/'));
                }
            }
        }

        return $mappedTags;
    }

    public function hasSepaInformation(string $message): bool
    {
        $sepaTags = $this->getSepaTags();

        foreach ($sepaTags as $tag) {
            if (strpos($message, $tag) !== false) {
                return true;
            }
        }

        return false;
    }

    private function getSepaTags(): array
    {
        return array_keys(
            array_merge(
                self::TAG_MAPPING,
                self::PARTY_TYPES
            )
        );
    }

    private function concatenateInformationLines(array $lines): string
    {
        $informationLines = filterLinesOfTypes($lines, [
            new LineType(LineType::InformationPart1),
            new LineType(LineType::InformationPart2),
            new LineType(LineType::InformationPart3)
        ]);

        if (empty($informationLines)) {
            return '';
        }

        $fullMessage = '';
        foreach ($informationLines as $line) {
            if (method_exists($line, 'getMessageOrStructuredMessage')) {
                $msg = $line->getMessageOrStructuredMessage()->getMessage();
            } else {
                $msg = $line->getMessage();
            }
            $fullMessage .= $msg ? $msg->getValue() : '';
        }

        return trim($fullMessage);
    }
}
