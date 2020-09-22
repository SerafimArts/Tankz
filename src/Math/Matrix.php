<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Math;

class Matrix implements \ArrayAccess
{
    public const M00 = 0;
    public const M01 = 3;
    public const M02 = 6;
    public const M10 = 1;
    public const M11 = 4;
    public const M12 = 7;
    public const M20 = 2;
    public const M21 = 5;
    public const M22 = 8;

    /**
     * @var array|float[]
     */
    public array $value = [];

    /**
     * @param array $matrix
     */
    public function __construct(array $matrix = [])
    {
        foreach (\range(0, 9) as $i) {
            $this->value[$i] = $matrix[$i] ?? 0;
        }
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        \assert(\is_int($offset));

        return isset($this->value[$offset]) || \array_key_exists($offset, $this->value);
    }

    /**
     * @param int $offset
     * @return float|int
     */
    public function offsetGet($offset): float
    {
        \assert(\is_int($offset) && $offset >= 0 && $offset <= 9);

        return (float)$this->value[$offset];
    }

    /**
     * @param int $offset
     * @param float $value
     */
    public function offsetSet($offset, $value): void
    {
        \assert(\is_int($offset) && $offset >= 0 && $offset <= 9);
        \assert(\is_float($value));

        $this->value[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        \assert(\is_int($offset) && $offset >= 0 && $offset <= 9);

        $this->value[$offset] = 0;
    }

    /**
     * Sets this matrix to a rotation matrix that will rotate any vector in
     * counter-clockwise direction around the z-axis.
     *
     * @param float $degrees the angle in degrees.
     * @return self This matrix for the purpose of chaining operations.
     */
    public function setToRotation(float $degrees): self
    {
        return $this->setToRotationRad(Utils::DEG_2_RAD * $degrees);
    }

    /**
     * Sets this matrix to a rotation matrix that will rotate any vector in
     * counter-clockwise direction around the z-axis.
     *
     * @param float $radians the angle in radians.
     * @return self This matrix for the purpose of chaining operations.
     */
    public function setToRotationRad(float $radians): self
    {
        $cos = \cos($radians);
        $sin = \sin($radians);

        $this->value[self::M00] = $cos;
        $this->value[self::M10] = $sin;
        $this->value[self::M20] = 0;

        $this->value[self::M01] = -$sin;
        $this->value[self::M11] = $cos;
        $this->value[self::M21] = 0;

        $this->value[self::M02] = 0;
        $this->value[self::M12] = 0;
        $this->value[self::M22] = 1;

        return $this;
    }

    /**
     * @param float $degrees
     * @return $this
     */
    public function rotate(float $degrees): self
    {
        return $this->rotateRadians(Utils::DEG_2_RAD * $degrees);
    }

    /**
     * Postmultiplies this matrix with a (counter-clockwise) rotation matrix.
     * Postmultiplication is also used by OpenGL ES'
     * 1.x glTranslate/glRotate/glScale.
     *
     * @param float $radians The angle in radians
     * @return self This matrix for the purpose of chaining.
     */
    public function rotateRadians(float $radians): self
    {
        if ($radians === 0.0) {
            return $this;
        }

        $cos = \cos($radians);
        $sin = \sin($radians);

        // @formatter:off
        $this->value = self::mulArrays($this->value, [
            $cos, $sin,  0,
            -$sin, $cos, 0,
            0,     0,    1,
        ]);
        // @formatter:on

        return $this;
    }

    /**
     * Multiplies matrix a with matrix b in the following manner:
     *
     * <pre>
     * mul(A, B) => A := AB
     * </pre>
     *
     * @param float[] $m1 The float array representing the first matrix. Must have at least 9 elements.
     * @param float[] $m2 The float array representing the second matrix. Must have at least 9 elements.
     * @return array|float[]
     */
    public static function mulArrays(array $m1, array $m2): array
    {
        $m1[self::M00] = $m1[self::M00] * $m2[self::M00] +
            $m1[self::M01] * $m2[self::M10] +
            $m1[self::M02] * $m2[self::M20];

        $m1[self::M01] = $m1[self::M00] * $m2[self::M01] +
            $m1[self::M01] * $m2[self::M11] +
            $m1[self::M02] * $m2[self::M21];

        $m1[self::M02] = $m1[self::M00] * $m2[self::M02] +
            $m1[self::M01] * $m2[self::M12] +
            $m1[self::M02] * $m2[self::M22];

        $m1[self::M10] = $m1[self::M10] * $m2[self::M00] +
            $m1[self::M11] * $m2[self::M10] +
            $m1[self::M12] * $m2[self::M20];

        $m1[self::M11] = $m1[self::M10] * $m2[self::M01] +
            $m1[self::M11] * $m2[self::M11] +
            $m1[self::M12] * $m2[self::M21];

        $m1[self::M12] = $m1[self::M10] * $m2[self::M02] +
            $m1[self::M11] * $m2[self::M12] +
            $m1[self::M12] * $m2[self::M22];

        $m1[self::M20] = $m1[self::M20] * $m2[self::M00] +
            $m1[self::M21] * $m2[self::M10] +
            $m1[self::M22] * $m2[self::M20];

        $m1[self::M21] = $m1[self::M20] * $m2[self::M01] +
            $m1[self::M21] * $m2[self::M11] +
            $m1[self::M22] * $m2[self::M21];

        $m1[self::M22] = $m1[self::M20] * $m2[self::M02] +
            $m1[self::M21] * $m2[self::M12] +
            $m1[self::M22] * $m2[self::M22];

        return $m1;
    }

    /**
     * Postmultiplies this matrix by a translation matrix. Postmultiplication
     * is also used by OpenGL ES' 1.x glTranslate/glRotate/glScale.
     *
     * @param Vector2 $vec2 The translation vector.
     * @return $this This matrix for the purpose of chaining.
     */
    public function translateVector2(Vector2 $vec2): self
    {
        return $this->translate($vec2->x, $vec2->y);
    }

    /**
     * Postmultiplies this matrix by a translation matrix. Postmultiplication
     * is also used by OpenGL ES' 1.x glTranslate/glRotate/glScale.
     *
     * @param float $x The x-component of the translation vector.
     * @param float $y The y-component of the translation vector.
     * @return self This matrix for the purpose of chaining.
     */
    public function translate(float $x, float $y): self
    {
        // @formatter:off
        $this->value = self::mulArrays($this->value, [
            1,  0,  0,
            0,  1,  0,
            $x, $y, 1,
        ]);
        // @formatter:on

        return $this;
    }

    /**
     * Postmultiplies this matrix with the provided matrix and stores the result
     * in this matrix.
     *
     * For example:
     *
     * <pre>
     * A.mul(B) results in A := AB
     * </pre>
     *
     * @param Matrix $matrix Matrix to multiply by.
     * @return self This matrix for the purpose of chaining operations together.
     */
    public function mul(Matrix $matrix): self
    {
        $this->value = self::mulArrays($this->value, $matrix->value);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \vsprintf("[ %d | %d | %d ]\n[ %d | %d | %d ]\n[ %d | %d | %d ]\n", $this->value);
    }
}
