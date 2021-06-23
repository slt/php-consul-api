<?php declare(strict_types=1);

namespace DCarbone\PHPConsulAPI;

/*
   Copyright 2020 Daniel Carbone (daniel.p.carbone@gmail.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

use DCarbone\Go\Time;
use DCarbone\PHPConsulAPI\Event\UserEvent;
use DCarbone\PHPConsulAPI\KV\KVPair;
use DCarbone\PHPConsulAPI\KV\KVTxnOp;
use DCarbone\PHPConsulAPI\Operator\ReadableDuration;

/**
 * Used to assist with hydrating json responses
 *
 * Trait Hydratable
 */
trait Hydratable
{
    /**
     * Marshal field is designed to replicate (to ao point) what Golang does during the json.Marshal call
     *
     * @param array $output
     * @param string $field
     * @param mixed $value
     */
    protected function marshalField(array &$output, string $field, $value): void
    {
        $def = static::FIELDS[$field] ?? null;

        // if this field has no special handling, set as-is and move on.
        if (null === $def) {
            $output[$field] = $value;
            return;
        }

        // if this field is marked as being "skipped", do not set, then move on.
        if (isset($def[Hydration::FIELD_SKIP]) && true === $def[Hydration::FIELD_SKIP]) {
            return;
        }

        // if this field is marked as needing to be typecast to a specific type for output
        if (isset($def[Hydration::FIELD_MARSHAL_AS])) {
            switch ($def[Hydration::FIELD_MARSHAL_AS]) {
                case Hydration::STRING:
                    $value = (string)$value;
                    break;
                case Hydration::INTEGER:
                    $value = (int)$value;
                    break;
                case Hydration::DOUBLE:
                    $value = (float)$value;
                    break;
                case Hydration::BOOLEAN:
                    $value = (bool)$value;
                    break;

                default:
                    throw new \InvalidArgumentException(
                        \sprintf('Unable to handle serializing to %s', $def[Hydration::FIELD_MARSHAL_AS])
                    );
            }
        }

        // if this field is not explicitly marked as "omitempty", set and move on.
        if (!isset($def[Hydration::FIELD_OMITEMPTY]) || true !== $def[Hydration::FIELD_OMITEMPTY]) {
            $output[$field] = $value;
            return;
        }

        // otherwise, handle value setting on a per-type basis

        $type = \gettype($value);

        // strings must be non empty
        if (Hydration::STRING === $type) {
            if ('' !== $value) {
                $output[$field] = $value;
            }
            return;
        }

        // integers must be non-zero (negatives are ok)
        if (Hydration::INTEGER === $type) {
            if (0 !== $value) {
                $output[$field] = $value;
            }
            return;
        }

        // floats must be non-zero (negatives are ok)
        if (Hydration::DOUBLE === $type) {
            if (0.0 !== $value) {
                $output[$field] = $value;
            }
            return;
        }

        // bools must be true
        if (Hydration::BOOLEAN === $type) {
            if ($value) {
                $output[$field] = $value;
            }
            return;
        }

        // object "non-zero" calculations require a bit more finesse...
        if (Hydration::OBJECT === $type) {
            // AbstractModels are collections, and are non-zero if they contain at least 1 entry
            if ($value instanceof FakeSlice || $value instanceof FakeMap) {
                if (0 < \count($value)) {
                    $output[$field] = $value;
                }
                return;
            }

            // Time\Duration types are non-zero if their internal value is > 0
            if ($value instanceof Time\Duration || $value instanceof ReadableDuration) {
                if (0 < $value->Nanoseconds()) {
                    $output[$field] = $value;
                }
                return;
            }

            // Time\Time values are non-zero if they are anything greater than epoch
            if ($value instanceof Time\Time) {
                if (!$value->IsZero()) {
                    $output[$field] = $value;
                }
                return;
            }

            // otherwise, by being defined it is non-zero, so add it.
            $output[$field] = $value;
            return;
        }

        // arrays must have at least 1 value
        if (Hydration::ARRAY === $type) {
            if ([] !== $value) {
                $output[$field] = $value;
            }
            return;
        }

        // todo: be more better about resources
        if (Hydration::RESOURCE === $type) {
            $output[$field] = $value;
            return;
        }

        // once we get here the only possible value type is "NULL", which are always considered "empty".  thus, do not
        // set any value.
    }

    /**
     * Attempts to hydrate the provided value into the provided field on the implementing class
     *
     * @param string $field
     * @param mixed $value
     */
    protected function hydrateField(string $field, $value): void
    {
        if (isset(static::FIELDS[$field])) {
            // if the implementing class has some explicitly defined overrides
            $this->hydrateComplex($field, $value, static::FIELDS[$field]);
        } elseif (!\property_exists($this, $field)) {
            // if the field isn't explicitly defined on the implementing class, just set it to whatever the incoming
            // value is
            $this->{$field} = $value;
        } /** @noinspection PhpStatementHasEmptyBodyInspection */ elseif (null === $value) {
            // if the value is null at this point, ignore and move on.
            // note: this is not checked prior to the property_exists call as if the field is not explicitly defined but
            // is seen with a null value, we still want to define it as null on the implementing type.
        } elseif (isset($this->{$field}) && \is_scalar($this->{$field})) {
            // if the property has a scalar default value, hydrate it as such.
            $this->hydrateScalar($field, $value, false);
        } else {
            // if we fall down here, try to set the value as-is.  if this barfs, it indicates we have a bug to be
            // squished.
            // todo: should this be an exception?
            $this->{$field} = $value;
        }
    }

    /**
     * @param array $fieldDef
     * @return bool
     */
    protected function fieldIsNullable(array $fieldDef): bool
    {
        // todo: make sure this key is always a bool...
        return $fieldDef[Hydration::FIELD_NULLABLE] ?? false;
    }

    /**
     * @param string $type
     * @return false|float|int|string|null
     */
    protected static function scalarZeroVal(string $type)
    {
        if (Hydration::STRING === $type) {
            return '';
        }
        if (Hydration::INTEGER === $type) {
            return 0;
        }
        if (Hydration::DOUBLE === $type) {
            return 0.0;
        }
        if (Hydration::BOOLEAN === $type) {
            return false;
        }

        return null;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param string $type
     * @param bool $nullable
     * @return bool|float|int|string
     */
    private function buildScalarValue(string $field, $value, string $type, bool $nullable)
    {
        // if the incoming value is null...
        if (null === $value) {
            // ...and this field is nullable, just return null
            if ($nullable) {
                return null;
            }
            // otherwise return zero val for this type
            return self::scalarZeroVal($type);
        }

        if (Hydration::STRING === $type) {
            return (string)$value;
        }
        if (Hydration::INTEGER === $type) {
            return \intval($value, 10);
        }
        if (Hydration::DOUBLE === $type) {
            return (float)$value;
        }
        if (Hydration::BOOLEAN === $type) {
            return (bool)$value;
        }

        // if we fall down to here, default to try to set the value to whatever it happens to be.
        return $value;
    }

    /**
     * @param string $field
     * @param array|object $value
     * @param string $class
     * @param bool $nullable
     * @return object|null
     */
    private function buildObjectValue(string $field, $value, string $class, bool $nullable): ?object
    {
        // if the incoming value is null...
        if (null === $value) {
            // ...and this field is nullable, return null
            if ($nullable) {
                return null;
            }
            // .. and this field must be an instance of the provided class, return empty new empty instance
            return new $class([]);
        }
        // if the incoming value is already an instance of the class, clone it and return
        if ($value instanceof $class) {
            return clone $value;
        }
        // otherwise, attempt to cast whatever was provided as an array and construct a new instance of $class
        if (KVPair::class === $class || KVTxnOp::class === $class || UserEvent::class === $class) {
            // special case for KVPair and KVTxnOp
            // todo: find cleaner way to do this...
            return new $class((array)$value, true);
        }
        return new $class((array)$value);
    }

    /**
     * Handles scalar type field hydration
     *
     * @param string $field
     * @param mixed $value
     * @param bool $nullable
     */
    private function hydrateScalar(string $field, $value, bool $nullable): void
    {
        $this->{$field} = $this->buildScalarValue(
            $field,
            $value,
            isset($this->{$field}) ? \gettype($this->{$field}) : Hydration::MIXED,
            $nullable
        );
    }

    /**
     * Handles complex type field hydration
     *
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private function hydrateComplex(string $field, $value, array $def): void
    {
        // check if a callable has been defined
        if (isset($def[Hydration::FIELD_CALLBACK])) {
            $cb = $def[Hydration::FIELD_CALLBACK];
            // allow for using a "setter" method
            if (\is_string($cb) && \method_exists($this, $cb)) {
                $this->{$cb}($value);
                return;
            }
            // handle all other callable input
            $err = \call_user_func($def[Hydration::FIELD_CALLBACK], $this, $field, $value);
            if (false === $err) {
                throw new \RuntimeException(
                    \sprintf(
                        'Error calling hydration callback "%s" for field "%s" on class "%s"',
                        \var_export($def[Hydration::FIELD_CALLBACK], true),
                        $field,
                        \get_class($this)
                    )
                );
            }
            return;
        }

        // try to determine field type by first looking up the field in the definition map, then by inspecting the
        // the field's default value.
        //
        // objects _must_ have an entry in the map, as they are either un-initialized at class instantiation time or
        // set to "NULL", at which point we cannot automatically determine the value type.

        if (isset($def[Hydration::FIELD_TYPE])) {
            // if the field has a FIELD_TYPE value in the definition map
            $type = $def[Hydration::FIELD_TYPE];
        } elseif (isset($this->{$field})) {
            // if the field is set and non-null
            $type = \gettype($this->{$field});
        } else {
            throw new \LogicException(
                \sprintf(
                    'Field "%s" on type "%s" is missing a FIELD_TYPE hydration entry: %s',
                    $field,
                    \get_class($this),
                    \var_export($def, true)
                )
            );
        }

        if (Hydration::OBJECT === $type) {
            $this->hydrateObject($field, $value, $def);
            return;
        }

        if (Hydration::ARRAY === $type) {
            $this->hydrateArray($field, $value, $def);
            return;
        }

        // at this point, assume scalar
        // todo: handle non-scalar types here
        $this->hydrateScalar($field, $value, self::fieldIsNullable($def));
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private function hydrateObject(string $field, $value, array $def): void
    {
        if (!isset($def[Hydration::FIELD_CLASS])) {
            throw new \LogicException(
                \sprintf(
                    'Field "%s" on type "%s" is missing FIELD_CLASS hydration entry: %s',
                    $field,
                    \get_class($this),
                    \var_export($def, true)
                )
            );
        }

        $this->{$field} = $this->buildObjectValue(
            $field,
            $value,
            $def[Hydration::FIELD_CLASS],
            self::fieldIsNullable($def)
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param array $def
     */
    private function hydrateArray(string $field, $value, array $def): void
    {
        // attempt to extract the two possible keys
        $type  = $def[Hydration::FIELD_ARRAY_TYPE] ?? null;
        $class = $def[Hydration::FIELD_CLASS]      ?? null;

        // type is required
        if (null === $type) {
            throw new \DomainException(
                \sprintf(
                    'Field "%s" on type "%s" definition is missing FIELD_ARRAY_TYPE value: %s',
                    $field,
                    \get_class($this),
                    \var_export($def, true)
                )
            );
        }

        // is the incoming value null?
        if (null === $value) {
            // if this value can be null'd, allow it.
            if (static::fieldIsNullable($def)) {
                $this->{$field} = null;
            }
            return;
        }

        // by the time we get here, $value must be an array
        if (!\is_array($value)) {
            throw new \RuntimeException(
                \sprintf(
                    'Field "%s" on type "%s" is an array but provided value is "%s"',
                    $field,
                    \get_class($this),
                    \gettype($value)
                )
            );
        }

        // currently the only supported array types are scalar or objects.  everything else will require
        // a custom callback for hydration purposes.

        if (Hydration::OBJECT === $type) {
            if (null === $class) {
                throw new \DomainException(
                    \sprintf(
                        'Field "%s" on type "%s" definition is missing FIELD_CLASS value: %s',
                        $field,
                        \get_class($this),
                        \var_export($def, true)
                    )
                );
            }

            foreach ($value as $k => $v) {
                // todo: causes double-checking for null if value isn't null, not great...
                if (null === $v) {
                    continue;
                }
                $this->{$field}[$k] = $this->buildObjectValue($field, $v, $class, false);
            }
        } else {
            // in all other cases, just set as-is
            foreach ($value as $k => $v) {
                if (null === $v) {
                    continue;
                }
                $this->{$field}[$k] = $this->buildScalarValue($field, $v, $type, false);
            }
        }
    }
}
