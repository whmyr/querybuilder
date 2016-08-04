<?php

namespace T3G\Querybuilder\Parser;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class QueryParser.
 */
class QueryParser
{
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_NOT_EQUAL = 'not_equal';
    const OPERATOR_IN = 'in';
    const OPERATOR_NOT_IN = 'not_in';
    const OPERATOR_BEGINS_WITH = 'begins_with';
    const OPERATOR_NOT_BEGINS_WITH = 'not_begins_with';
    const OPERATOR_CONTAINS = 'contains';
    const OPERATOR_NOT_CONTAINS = 'not_contains';
    const OPERATOR_ENDS_WITH = 'ends_with';
    const OPERATOR_NOT_ENDS_WITH = 'not_ends_with';
    const OPERATOR_IS_EMPTY = 'is_empty';
    const OPERATOR_IS_NOT_EMPTY = 'is_not_empty';
    const OPERATOR_IS_NULL = 'is_null';
    const OPERATOR_IS_NOT_NULL = 'is_not_null';
    const OPERATOR_LESS = 'less';
    const OPERATOR_LESS_OR_EQUAL = 'less_or_equal';
    const OPERATOR_GREATER = 'greater';
    const OPERATOR_GREATER_OR_EQUAL = 'greater_or_equal';
    const OPERATOR_BETWEEN = 'between';
    const OPERATOR_NOT_BETWEEN = 'not_between';

    const CONDITION_AND = 'AND';
    const CONDITION_OR = 'OR';

    /**
     * @param \stdClass $queryObject
     * @param string $table
     *
     * @return string
     */
    public function parse($queryObject, $table)
    {
        $condition = $queryObject->condition === self::CONDITION_AND ? self::CONDITION_AND : self::CONDITION_OR;
        $whereParts = [];
        if (!empty($queryObject->rules)) {
            foreach ($queryObject->rules as $rule) {
                if ($rule->condition && $rule->rules) {
                    $whereParts[] = $this->parse($rule, $table);
                } else {
                    $whereParts[] = $this->getWhereClause($rule, $table);
                }
            }
        }

        return ' ( ' . implode(' ' . $condition . ' ', $whereParts) . ' ) ';
    }

    /**
     * @param \stdClass $rule
     * @param string    $table
     *
     * @return string
     */
    protected function getWhereClause($rule, $table)
    {
        $dbConnection = $this->getDatabaseConnection();
        $where = '';
        $field = $dbConnection->quoteStr($rule->field, $table) . ' ';
        $value = is_string($rule->value) ? $dbConnection->fullQuoteStr($rule->value, $table) : null;
        switch ($rule->operator) {
            case self::OPERATOR_EQUAL:
                $where = $field . '=' . $value;
                break;
            case self::OPERATOR_NOT_EQUAL:
                $where = $field . '!=' . $value;
                break;
            case self::OPERATOR_IN:
            case self::OPERATOR_NOT_IN:
                $values = GeneralUtility::trimExplode(',', $rule->value);
                $escapedValues = [];
                foreach ($values as $value) {
                    $escapedValues[] = $dbConnection->fullQuoteStr($value, $table);
                }
                $negation = $rule->operator === self::OPERATOR_NOT_IN ? 'NOT ' : '';
                $where = $field . $negation . 'IN (' . implode(',', $escapedValues) . ')';
                break;
            case self::OPERATOR_BEGINS_WITH:
            case self::OPERATOR_NOT_BEGINS_WITH:
                $value = $dbConnection->escapeStrForLike($rule->value, $table);
                $negation = $rule->operator === self::OPERATOR_NOT_BEGINS_WITH ? 'NOT ' : '';
                $where = $field . $negation . 'LIKE ' . $dbConnection->fullQuoteStr($value . '%', $table);
                break;
            case self::OPERATOR_CONTAINS:
            case self::OPERATOR_NOT_CONTAINS:
                $value = $dbConnection->escapeStrForLike($rule->value, $table);
                $negation = $rule->operator === self::OPERATOR_NOT_CONTAINS ? 'NOT ' : '';
                $where = $field . $negation . 'LIKE ' . $dbConnection->fullQuoteStr('%' . $value . '%', $table);
                break;
            case self::OPERATOR_ENDS_WITH:
            case self::OPERATOR_NOT_ENDS_WITH:
                $value = $dbConnection->escapeStrForLike($rule->value, $table);
                $negation = $rule->operator === self::OPERATOR_NOT_ENDS_WITH ? 'NOT ' : '';
                $where = $field . $negation . 'LIKE ' . $dbConnection->fullQuoteStr('%' . $value, $table);
                break;
            case self::OPERATOR_IS_EMPTY:
            case self::OPERATOR_IS_NOT_EMPTY:
                $negation = $rule->operator === self::OPERATOR_IS_NOT_EMPTY ? '!' : '';
                $where = $field . $negation . '= \'\'';
                break;
            case self::OPERATOR_IS_NULL:
            case self::OPERATOR_IS_NOT_NULL:
                $negation = $rule->operator === self::OPERATOR_IS_NOT_NULL ? 'NOT' : '';
                $where = $field . 'IS ' . $negation . ' NULL';
                break;
            case self::OPERATOR_LESS:
                $where = $field . '<' . $value;
                break;
            case self::OPERATOR_LESS_OR_EQUAL:
                $where = $field . '<=' . $value;
                break;
            case self::OPERATOR_GREATER:
                $where = $field . '>' . $value;
                break;
            case self::OPERATOR_GREATER_OR_EQUAL:
                $where = $field . '>=' . $value;
                break;
            case self::OPERATOR_BETWEEN:
            case self::OPERATOR_NOT_BETWEEN:
                $negation = $rule->operator === self::OPERATOR_NOT_BETWEEN ? 'NOT ' : '';
                $value1 = $dbConnection->fullQuoteStr($rule->value[0], $table);
                $value2 = $dbConnection->fullQuoteStr($rule->value[1], $table);
                $where = $field . $negation . ' BETWEEN ' . $value1 . ' AND' . $value2;
                break;
        }

        return $where;
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
