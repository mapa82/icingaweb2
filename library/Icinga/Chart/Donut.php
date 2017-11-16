<?php

namespace Icinga\Chart;

class Donut
{
    protected $radius = 100 / (2 * M_PI);

    protected $centerColor = '#fff';

    protected $ringColor = '#eee';

    protected $slices = array();

    protected $count = 0;

    public function addSlice($data, $attributes = array())
    {
        $this->slices[] = array($data, $attributes);

        $this->count += $data;

        return $this;
    }

    protected function assemble()
    {
        $svg = array(
            'tag'        => 'svg',
            'attributes' => array(
                'xmlns'     => 'http://www.w3.org/2000/svg', 'viewbox' => '0 0 40 40',
                'max-width' => '100%', 'max-height' => '100%',
                'style'     => 'flex: 1;'
            ),
            'content' => array()
        );

        $svg['content'][] = array(
            'tag'        => 'circle',
            'attributes' => array(
                'cx'   => 20,
                'cy'   => 20,
                'r'    => $this->radius,
                'fill' => $this->centerColor
            )
        );

        $svg['content'][] = array(
            'tag'        => 'circle',
            'attributes' => array(
                'cx'           => 20,
                'cy'           => 20,
                'r'            => $this->radius,
                'fill'         => 'transparent',
                'stroke'       => $this->ringColor,
                'stroke-width' => 4
            )
        );

        $slices = $this->slices;

        array_walk($slices, function(&$slice) {
            $slice[0] = round(100 / $this->count * $slice[0], 1);
        });

        array_multisort(array_map(function ($slice) {
            return $slice[0];
        }, $slices), SORT_DESC, $slices);

        $g = array(
            'tag'        => 'g',
            'attributes' => array(
                'transform' => 'rotate(-90 20 20)'
            ),
            'content' => array()
        );

        $offset = 0;

        foreach ($slices as $slice) {
            $g['content'][] = array(
                'tag'        => 'circle',
                'attributes' => $slice[1] + array(
                    'cx'                => 20,
                    'cy'                => 20,
                    'r'                 => $this->radius,
                    'fill'              => 'transparent',
                    'stroke-width'      => 4,
                    'stroke-dasharray'  => $slice[0] . ' ' . (100 - $slice[0]),
                    'stroke-dashoffset' => $offset
                )
            );

            $offset += (100 - $slice[0]);
        }

        $svg['content'][] = $g;

        $svg['content'][] = array(
            'tag'        => 'text',
            'attributes' => array(
                'x'                  => '50%',
                'y'                  => '50%',
                'text-anchor'        => 'middle',
                'alignment-baseline' => 'middle'
            ),
            'content' => $this->count
        );

        return $svg;
    }

    protected function encode($content)
    {
        // TODO(el): Implement
        return $content;
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

$donut = new Donut();
$donut->addSlice(35, array('stroke' => 'red'));
$donut->addSlice(4546, array('stroke' => 'green'));
$donut->addSlice(789, array('stroke' => 'yellow'));

echo $donut->render();
