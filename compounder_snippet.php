<?php
//id - parent ID

if ( !isset($id) || $id == '' ) {
    return 'Please, set parent ID!';
}

$c = $modx->newQuery('modResource');
$c->where(array(
    'published' => 1,
    'parent' => $id
));
$c->sortby('menuindex', 'ASC');

$docs = $modx->getCollection('modResource', $c);
$html_output = '';

foreach ($docs as $doc) {

    $tmpl_res = $modx->getObject('modTemplate', array('id' => $doc->get('template')));

    if ($tmpl_res = $modx->getObject('modTemplate', array('id' => $doc->get('template')))){
        $template = $tmpl_res->get('content');
    }else{
        return '<b>ERROR:</b> can\'t find template: ' . $doc->get('template');
    }

    $placeholders = array(
        '[[*longtitle]]',
        '[[*pagetitle]]',
        '[[*menutitle]]',
        '[[*description]]',
        '[*introtext*]',
        '[[*content]]',
        '[[*id]]'
    );

    $doc_properties = array(
        $doc->get('longtitle'),
        $doc->get('pagetitle'),
        $doc->get('menutitle'),
        $doc->get('description'),
        $doc->get('introtext'),
        $doc->get('content'),
        $doc->get('id')
    );

    $tmp = str_replace($placeholders, $doc_properties, $template);
    //second parse to set placeholders in fields
    $tmp = str_replace($placeholders, $doc_properties, $tmp);

    $tvs_match = array();

    preg_match_all('/\[\[\*([a-zA-Z0-9_-]*)\]\]/i', $tmp, $tvs_match);
    if ( !empty($tvs_match[1]) ) {
        $tvs_names = $tvs_match[1];
        $search_arr = array();
        $replace_arr = $tvs_names;
        for ( $i = 0; $i < count($tvs_names); $i++ ) {
            $search_arr[$i] = '[[*' . $tvs_names[$i] . ']]';
            $replace_arr[$i] = $doc->getTVValue($tvs_names[$i]);
        }
        $tmp = str_replace($search_arr, $replace_arr, $tmp);
    }

    $html_output .= $tmp;
}

return $html_output;
