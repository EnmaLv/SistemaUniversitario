<?php

namespace App\Support\Seguridad;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSeguro
{
    private const ETIQUETAS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'span', 'div', 'ul', 'ol', 'li', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'figure', 'hr',
    ];

    private const ATRIBUTOS = [
        'a'  => ['href', 'title'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public static function limpiar(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $documento = new DOMDocument('1.0', 'UTF-8');
        $previo = libxml_use_internal_errors(true);
        $documento->loadHTML('<?xml encoding="UTF-8"><div id="raiz-html-seguro">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previo);

        $raiz = $documento->getElementById('raiz-html-seguro');
        if (! $raiz) {
            return e(strip_tags($html));
        }

        static::depurar($raiz);

        $salida = '';
        foreach ($raiz->childNodes as $hijo) {
            $salida .= $documento->saveHTML($hijo);
        }

        return $salida;
    }

    private static function depurar(DOMNode $nodo): void
    {
        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            if ($hijo instanceof DOMElement) {
                $etiqueta = strtolower($hijo->tagName);

                if (in_array($etiqueta, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'base'], true)) {
                    $nodo->removeChild($hijo);
                    continue;
                }

                static::depurar($hijo);

                if (! in_array($etiqueta, self::ETIQUETAS, true)) {
                    while ($hijo->firstChild) {
                        $nodo->insertBefore($hijo->firstChild, $hijo);
                    }
                    $nodo->removeChild($hijo);
                    continue;
                }

                $permitidos = self::ATRIBUTOS[$etiqueta] ?? [];
                foreach (iterator_to_array($hijo->attributes) as $atributo) {
                    $nombre = strtolower($atributo->name);
                    if (! in_array($nombre, $permitidos, true) || ($nombre === 'href' && ! static::urlSegura($atributo->value))) {
                        $hijo->removeAttribute($atributo->name);
                    }
                }

                if ($etiqueta === 'a' && $hijo->hasAttribute('href')) {
                    $hijo->setAttribute('rel', 'noopener noreferrer nofollow');
                }
            } elseif ($hijo->nodeType === XML_COMMENT_NODE || $hijo->nodeType === XML_PI_NODE) {
                $nodo->removeChild($hijo);
            }
        }
    }

    private static function urlSegura(string $url): bool
    {
        $url = trim(preg_replace('/[\x00-\x20]+/', '', $url));

        return (bool) preg_match('#^(https?://|mailto:|/(?!/)|\#)#i', $url);
    }
}
