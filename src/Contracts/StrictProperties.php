<?php

namespace Chriha\DataObjects\Contracts;

/**
 * Implement this interface, if you want to throw a FailedStrictPropertiesException.
 * It will only throw the exception, if the data cannot be mapped via the MapFrom
 * or MapFromOneOf attributes or via the property name. It also requires the data
 * object property to not have a default value or be nullable.
 *
 * @see DataObject::fill()
 */
interface StrictProperties
{
}
