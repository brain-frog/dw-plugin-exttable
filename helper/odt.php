<?php
/**
 * ODT (Open Document format) export for Exttab3 plugin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Lars (LarsDW223)
 */

if(!defined('DOKU_INC')) die();

class helper_plugin_exttab3_odt extends DokuWiki_Plugin {

    function render(Doku_Renderer $renderer, $data) {
        $properties = array();

        // Return if installed ODT plugin version is too old.
        if ( method_exists($renderer, 'getODTProperties') == false ||
             method_exists($renderer, '_odtTableAddColumnUseProperties') == false ) {
            return false;
        }

        //list($tag, $state, $match) = $data;
        list($state, $tag, $attr) = $data;

        // Get style content
        $style = '';
        if (preg_match('/style=".*"/', $attr, $matches) === 1) {
            $style = substr($matches[0], 6);
            $style = trim($style, ' "');
        }

        // class to get CSS Properties by $render->getODTProperties()
        $class = 'dokuwiki exttab3';

        switch ( $state ) {
            case DOKU_LEXER_ENTER:    // open tag
                // Get CSS properties for ODT export.
                $renderer->getODTProperties($properties, $tag, $class, $style);

                switch ($tag) {
                    case 'table':
                        $renderer->_odtTableOpenUseProperties($properties);
                        break;
                    case 'caption':
                        $renderer->_odtTableRowOpenUseProperties($properties);

                        // Parameter 'colspan=0' indicates spann across all columns!
                        $renderer->_odtTableHeaderOpenUseProperties($properties, 0, 1);
                        break;
                    case 'th':
                        $renderer->_odtTableHeaderOpenUseProperties($properties);
                        $renderer->_odtTableAddColumnUseProperties($properties);
                        break;
                    case 'tr':
                        $renderer->_odtTableRowOpenUseProperties($properties);
                        break;
                    case 'td':
                        $renderer->_odtTableCellOpenUseProperties($properties);
                        break;
                }
                break;
            case DOKU_LEXER_MATCHED:  // defensive, shouldn't occur
                break;
            case DOKU_LEXER_UNMATCHED:
                $renderer->cdata($tag);
                break;
            case DOKU_LEXER_EXIT:     // close tag
                switch ($tag) {
                    case 'table':
                        //$renderer->table_close();
                        $renderer->_odtTableClose();
                        break;
                    case 'caption':
                        $renderer->tableheader_close();
                        $renderer->tablerow_close();
                        break;
                    case 'th':
                        $renderer->tableheader_close();
                        break;
                    case 'tr':
                        $renderer->tablerow_close();
                        break;
                    case 'td':
                        $renderer->p_close();
                        $renderer->tablecell_close();
                        break;
                }
                break;
        }
    }
}
