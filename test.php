<?php
class NetworkOperator
{
    /**
     * Country Prefix.
     */
    const PREFIX = '237';

    /**
     * Operator Prefixes.
     */
    const OPERATOR_PREFIXES = [
        'mtn' => [
            67,
            650,
            651,
            652,
            653,
            654,
            680,
            681,
            682,
            683,
        ],
        'orange' => [
            69,
            655,
            656,
            657,
            658,
            659,
        ],
        'nexttel' => [
            66,
        ],
        'camtel' => [
            233,
            222,
            242,
            243,
        ],
    ];

    /**
     * Match Regex to Operator.
     * @param string|null $operator
     * @return string
     */
    public static function getRegex(string $operator = null): ?string
    {
        if (!$operator || !array_key_exists($operator, self::OPERATOR_PREFIXES))
            return 'null';
        $operator_prefixes = trim(implode('|', self::OPERATOR_PREFIXES[$operator]), '|');
        return "((|(0{2}))?" . self::PREFIX . ")?((" . "$operator_prefixes" . ")([0-9]{6,7}))$";
    }
}

echo NetworkOperator::getRegex("orange"), PHP_EOL;