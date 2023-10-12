<?php


namespace Nish\Commons;


trait ObjectArrayConversionTrait
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array $attrValues
     * @return self
     */
    public static function fromArray(array $attrValues = [])
    {
        $obj = new self();

        foreach ($attrValues as $name => $value) {
            if (array_key_exists($name, get_object_vars($obj))) {
                call_user_func([$obj, 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name)))], $value);
            }
        }

        return $obj;
    }
}