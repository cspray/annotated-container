<?php

namespace Cspray\AnnotatedContainer\LogicalConstraint;

/**
 * Details whether a LogicalConstraintViolation might be a critical error or simply a code sniff that something might
 * go wrong.
 */
enum LogicalConstraintViolationType {
    case Warning;
    case Notice;
}