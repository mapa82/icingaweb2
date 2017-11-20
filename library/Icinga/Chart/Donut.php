<?php

namespace Icinga\Chart;

/** Donut chart implementation */
class Donut
{
    /**
     * Big label in the middle of the donut, color is critical (red)
     *
     * @var string
     */
    protected $labelBig;

    /**
     * Small label in the lower part of the donuts hole
     *
     * @var string
     */
    protected $labelSmall;

    /**
     * Thickness of the donut ring
     *
     * @var int
     */
    protected $thickness = 6;

    /**
     * Radius based of 100 to simplify the calculations
     *
     * 100 / (2 * M_PI)
     *
     * @var float
     */
    protected $radius = 15.9154943092;

    /**
     * Color of the hole in the donut
     *
     * Transparent by default so it can be placed anywhere with ease
     *
     * @var string
     */
    protected $centerColor = 'transparent';

    /**
     * The different colored parts that represent the data
     *
     * @var array
     */
    protected $slices = array();

    /**
     * The total amount of data units
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Adds a colored part that represent the data
     *
     * @param   integer     $data           Units of data
     * @param   array       $attributes     HTML attributes for this slice. (For example ['class' => 'slice-state-ok'])
     *
     * @return  $this
     */
    public function addSlice($data, $attributes = array())
    {
        $this->slices[] = array($data, $attributes);

        $this->count += $data;

        return $this;
    }

    /**
     * Set the thickness for this Donut
     *
     * @param   integer $thickness
     *
     * @return  $this
     */
    public function setThickness($thickness) {
        $this->thickness = $thickness;

        return $this;
    }

    /**
     * Set the center color for this Donut
     *
     * @param   string  $centerColor
     *
     * @return  $this
     */
    public function setCenterColor($centerColor) {
        $this->centerColor = $centerColor;

        return $this;
    }

    /**
     * Set the text of the big label
     *
     * @param   string  $labelBig
     *
     * @return  $this
     */
    public function setLabelBig($labelBig) {
        $this->labelBig = $labelBig;

        return $this;
    }

    /**
     * Set the text of the small label
     *
     * @param   string  $labelSmall
     *
     * @return  $this
     */
    public function setLabelSmall($labelSmall) {
        $this->labelSmall = $labelSmall;

        return $this;
    }

    /**
     * Put together all slices of this Donut
     *
     * @return  array   $svg
     */
    protected function assemble()
    {
        // svg tag containing the ring
        $svg = array(
            'tag'        => 'svg',
            'attributes' => array(
                'xmlns'         => 'http://www.w3.org/2000/svg',
                'viewbox'       => '0 0 40 40',
                'class'         => 'svg-donut-graph'
            ),
            'content' => array()
        );

        // Donut hole
        $svg['content'][] = array(
            'tag'        => 'circle',
            'attributes' => array(
                'cx'   => 20,
                'cy'   => 20,
                'r'    => $this->radius,
                'fill' => 'transparent'
            )
        );

        // When there is no data show gray circle
        $svg['content'][] = array(
            'tag'        => 'circle',
            'attributes' => array(
                'cx'           => 20,
                'cy'           => 20,
                'r'            => $this->radius,
                'fill'         => 'transparent',
                'stroke'       => '#ddd',
                'stroke-width' => $this->thickness
            )
        );

        $slices = $this->slices;

        if ($this->count !== 0) {
            array_walk($slices, function (&$slice) {

                $slice[0] = round(100 / $this->count * $slice[0], 2);
            });
        }

        // on 0 the donut would start at "3 o'clock" and the offset shifts counterclockwise
        $offset = 25;

        foreach ($slices as $slice) {
            $svg['content'][] = array(
                'tag'        => 'circle',
                'attributes' => $slice[1] + array(
                    'cx'                => 20,
                    'cy'                => 20,
                    'r'                 => $this->radius,
                    'fill'              => 'transparent',
                    'stroke-width'      => $this->thickness,
                    'stroke-dasharray'  => $slice[0] . ' ' . (99.9 - $slice[0]), // 99.9 prevents gaps (overlap slightly)
                    'stroke-dashoffset' => $offset
                )
            );
            // negative values shift in the clockwise direction
            $offset -= $slice[0];
        }

        if (isset($this->labelBig) || isset($this->labelSmall)) {
            $text = array(
                'tag' => 'g',
                'attributes' => array(
                    'class' => 'svg-donut-label'
                ),
                'content' => array()
            );

            if (isset($this->labelBig)) {
                $text['content'][] = array(
                    'tag' => 'text',
                    'attributes' => array(
                        'class' => 'svg-donut-label-big-red',
                        'x' => '50%',
                        'y' => '50%'
                    ),
                    'content' => $this->shortenLabel($this->labelBig)
                );
            }

            if (isset($this->labelSmall)) {
                $text['content'][] = array(
                    'tag' => 'text',
                    'attributes' => array(
                        'class' => 'svg-donut-label-small',
                        'x' => '50%',
                        'y' => '50%'
                    ),
                    'content' => $this->labelSmall
                );
            }

            $svg['content'][] = $text;
        }

        return $svg;
    }

    /**
     * Shorten the label to 3 digits if it is numeric
     *
     * 10 => 10 ... 1111 => ~1k ... 1888 => ~2k
     *
     * @param   int|string  $label
     *
     * @return  string
     */
    protected function shortenLabel($label) {
        if (is_numeric($label) && strlen($label) > 3) {

            return '~' . substr(round($label, -3), 0, 1) . 'k';
        }

        return $label;
    }

    protected function encode($content)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $replaceFlags = ENT_COMPAT | ENT_SUBSTITUTE | ENT_HTML5;
        } else {
            $replaceFlags = ENT_COMPAT | ENT_IGNORE;
        }

        return htmlspecialchars($content, $replaceFlags, 'UTF-8', true);
    }

    protected function renderAttributes(array $attributes)
    {
        $html = [];

        foreach ($attributes as $name => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value) && $value) {
                $html[] = $name;
                continue;
            }

            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            $html[] = "$name=\"" . $this->encode($value) . '"';
        }

        return implode(' ', $html);
    }

    protected function renderContent(array $element)
    {
        $tag = $element['tag'];
        $attributes = isset($element['attributes']) ? $element['attributes'] : array();
        $content = isset($element['content']) ? $element['content'] : null;

        $html = array(
            // rtrim because attributes may be empty
            rtrim("<$tag " . $this->renderAttributes($attributes))
            . ">"
        );

        if ($content !== null) {
            if (is_array($content)) {
                foreach ($content as $child) {
                    $html[] = is_array($child) ? $this->renderContent($child) : $this->encode($child);
                }
            } else {
                $html[] = $this->encode($content);
            }
        }

        $html[] = "</$tag>";

        return implode("\n", $html);
    }

    public function render()
    {
        $svg = $this->assemble();

        return $this->renderContent($svg);
    }
}
