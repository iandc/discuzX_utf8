<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * lib_func Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_func 
{    
    /**
     * paging
     *
     * @access public
     * @param int, int, string
     * @return string
     */
    public static function paging($page_count, $page_index = 1, $page_url, $limit = 10, $count = 0)
    {
        if ($page_index < 1 || $page_index > $page_count){
            $page_index = 1;
        }
        
        $html = '<div class="layui-box layui-laypage layui-laypage-default">';
    
        if($page_index <= 1)
            $html .= '<a href="javascript:;" class="layui-laypage-prev layui-disabled"><i class="layui-icon">'.lib_base::lang('func_page_prev').'</i></a>';
        else
            $html .= '<a href="'.$page_url.($page_index - 1).'" class="layui-laypage-prev"><i class="layui-icon">'.lib_base::lang('func_page_prev').'</i></a>';
        
        if($page_index > 4)
            $html .= '<a href="'.$page_url.(1).'" class="layui-laypage-first">1</a>';
        
        if($page_index > 5)
            $html .= '<span class="layui-laypage-spr">'.lib_base::lang('func_page_intval').'</span>';
         
        if ($page_index - 3 > 0)
            $html .= '<a href="'.$page_url.($page_index - 3).'">'.($page_index - 3).'</a>';
        if ($page_index - 2 > 0)
            $html .= '<a href="'.$page_url.($page_index - 2).'">'.($page_index - 2).'</a>';
        if ($page_index - 1 > 0)
            $html .= '<a href="'.$page_url.($page_index - 1).'">'.($page_index - 1).'</a>';
         
        $html .= '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>'.$page_index.'</em></span>';
         
        if ($page_index + 1 <= $page_count)
            $html .= '<a href="'.$page_url.($page_index + 1).'">'.($page_index + 1).'</a>';
        if ($page_index + 2 <= $page_count)
            $html .= '<a href="'.$page_url.($page_index + 2).'">'.($page_index + 2).'</a>';
        if ($page_index + 3 <= $page_count)
            $html .= '<a href="'.$page_url.($page_index + 3).'">'.($page_index + 3).'</a>';
        
        if($page_count - $page_index > 4)
        $html .= '<span class="layui-laypage-spr">'.lib_base::lang('func_page_intval').'</span>';
        
        if($page_count - $page_index >= 4)
            $html .= '<a href="'.$page_url.$page_count.'" class="layui-laypage-last">'.$page_count.'</a>';
        
        if($page_index >= $page_count)
            $html .= '<a href="javascript:;" class="layui-laypage-next layui-disabled"><i class="layui-icon">'.lib_base::lang('func_page_next').'</i></a>';
        else
            $html .= '<a href="'.$page_url.($page_index + 1).'" class="layui-laypage-next"><i class="layui-icon">'.lib_base::lang('func_page_next').'</i></a>';

        $html .= '<span class="layui-laypage-skip">'.lib_base::lang('func_page_to').'<input name="page" type="text" value="1" class="layui-input">'.lib_base::lang('func_page_page').'<button type="button" class="layui-laypage-btn" onclick="$(\'body\').find(\'form\').submit()" lay-ignore="">'.lib_base::lang('func_page_submit').'</button></span>';
        $count && $html .= '<span class="layui-laypage-count">'.lib_base::lang('func_page_count').' '.$count.' '.lib_base::lang('func_page_number').'</span>';
        $html .= '<span class="layui-laypage-limits"><select name="limit" onchange="$(\'body\').find(\'form\').submit()" lay-ignore="">';
        for($i = 1; $i <= 9; $i++){
            $count = $i * 10;
            $selected = $count == $limit ? 'selected' : '';
            $html .= '<option value="'.$count.'"'.$selected.'>'.$count.' '.lib_base::lang('func_page_number').'/'.lib_base::lang('func_page_page').'</option>';
        }
        $html .= '</select></span>';
         
        $html  .= '</div>';
         
        return $html;
    }
    
    /**
     * select opotion
     *
     * @access public
     * @param array, string
     * @return string
     */
    public static function select_option($option, $select = '')
    {
        $html = '';
        foreach ($option as $key => $value){
            $selected = ($select&&$select==$key)?' selected':'';
            $html .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
        }
    
        return $html;
    }
    
    /**
     * option field
     *
     * @access public
     * @param array
     * @return string
     */
    public static function field_option($data, $select = '')
    {
        $option = '';
        foreach ($data as $key => $value){
            $position = strpos($key, 'AS');
            if($position != false){
                $key = substr($key, 0, $position - 1);
            }
            
            $selected = $key == $select ? ' selected' : '';
            $option .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
        }
    
        return $option;
    }
    
    /**
     * field string
     *
     * @access public
     * @param array
     * @return string
     */
    public static function field_str($data)
    {
        $str = '';
        foreach ($data as $key => $value){
            $str .= $key.',';
        }
        $str = substr($str, 0, strlen($str)-1);
    
        return $str;
    }
    
    /**
     * create table
     *
     * @access public
     * @param array, array, array
     * @return string
     */
    public static function create_table($contents, $fields, $formate = array())
    {
        if(! $contents){
            return '<tr><td style="text-align:center;padding:10px 10px;">'.lib_base::lang('nodata').'</td></tr>';
        }
         
        $html_table = '<colgroup>';
        if(isset($formate['batch'])){
            $html_table .= '<col width="48">';
        }

        if(isset($formate['col'])){
            foreach($contents[0] as $key => $value){
                if(isset($formate['col'][$key])){
                    $html_table .= '<col width="'.$formate['col'][$key].'">';
                }else if(isset($formate['time']) && in_array($key, $formate['time'])){
                    $html_table .= '<col width="142">';
                }else{
                    $html_table .= '<col>';
                }
            }
        }else{
            foreach($contents[0] as $key => $value){
                if(isset($formate['time']) && in_array($key, $formate['time'])){
                    $html_table .= '<col width="142">';
                }else{
                    $html_table .= '<col>';
                }
            }
        }
         
        if(isset($formate['op'])){
            $html_table .= '<col width="50">';
        }
         
        $html_table .= '</colgroup>';
    
        $html_table .= '<thead><tr>';
        if(isset($formate['batch'])){
            $html_table .= '<th><div class="layui-table-cell"><input type="checkbox" lay-skin="primary" lay-filter="allChoose" id="allChoose"></div></th>';
        }
    
        foreach ($fields as $key => $value){
            if(isset($formate['order']) && in_array($key, $formate['order'])){
                $html_table .= '<th><div class="layui-table-cell"><a href="javascript:;" onclick="Func.location_order(\''.$key.'\')">'.$value.'</a></div></th>';
            }else{
                $html_table .= '<th><div class="layui-table-cell">'.$value.'</div></th>';
            }
        }
    
        if(isset($formate['op'])){
            $html_table .= '<th><div class="layui-table-cell" align="center">'.lib_base::lang('op').'</div></th>';
        }
    
        $html_table .= '</tr></thead><tbody>';
    
        $key_first = array_keys(current($contents));
        foreach ($contents as $key => $value)
        {
            $id = $value[$key_first[0]];
            	
            $html_table .= '<tr>';
            if(isset($formate['batch'])){
                $html_table .= '<td><div class="layui-table-cell"><input type="checkbox" name="batch[]" lay-skin="primary" lay-filter="choose" value="'.$id.'"></div></td>';
            }
            
            foreach ($value as $k => $val)
            {
                $html_table .= '<td><div class="layui-table-cell">';

                if(isset($formate['time']) && in_array($k, $formate['time']) && $val){
                    $html_table .= date('Y-m-d H:i', $val);
                }else if(isset($formate['pic']) && in_array($k, $formate['pic']) && $val){
                    $html_table .= '<img src="'.$val.'" style="max-width:70px;"/>';
                }else if(isset($formate['fi'][$k])){
                    $html_table .= $formate['fi'][$k][$val];
                }else if(isset($formate['edit']) && in_array($k, $formate['edit']['field'])){
                    $html_table .= '<a href="javascript:;" onclick="Func.edit(\''.$formate['edit']['url'].'\', \''.$k.'\', '.$id.')" id="'.$k.'_'.$id.'">'.$val.'</a>';
                }else{
                    $html_table .= $val;
                }
    
                $html_table .= '</div></td>';
            }
            	
            if(isset($formate['op'])){
                $html_table .= '<td><div class="layui-table-cell" style="white-space:nowrap;">';
                foreach($formate['op'] as $val){
                    $icon = '';
                    $color = 'layui-btn-primary ';
                    if($val['name'] == lib_base::lang('edit')){
                        $icon = '<i class="layui-icon layui-icon-edit"></i>';
                        $color = 'layui-btn-normal ';
                    }else if($val['name'] == lib_base::lang('delete')){
                        $icon = '<i class="layui-icon layui-icon-delete"></i>';
                        $color = 'layui-btn-danger ';
                    }

                    if(isset($val['type'])){
                        if($val['type'] == 1){
                            $html_table .= '<a class="layui-btn '.$color.'layui-btn-xs" target="_blank" href="'.$val['url'].$id.'">'.$icon.$val['name'].'</a>';
                        }elseif($val['type'] == 2){
                            $html_table .= '<a class="layui-btn '.$color.'layui-btn-xs" onclick="'.$val['url'].'('.$id.');">'.$icon.$val['name'].'</a>';
                        }elseif($val['type'] == 3){
                            $confirm = (isset($val['confirm'])&&$val['confirm'] === FALSE)?'':',confirm:1';
                            $html_table .= '<a class="layui-btn '.$color.'layui-btn-xs" onclick="Func.ajax({url:\''.$val['url'].'\',ids:\''.$id.'\''.$confirm.'});">'.$icon.$val['name'].'</a>';
                        }else{
                            $html_table .= '<a class="layui-btn '.$color.'layui-btn-xs" onclick="Func.open({iframe:true,url:\''.$val['url'].$id.'\'});">'.$icon.$val['name'].'</a>';
                        }
                    }else{
                        $html_table .= '<a class="layui-btn '.$color.'layui-btn-xs" href="javascript:Func.open({iframe:true,url:\''.$val['url'].$id.'\'});">'.$icon.$val['name'].'</a>';
                    }
                }
                	
                $html_table .= '</div></td>';
            }
            	
            $html_table .= '</tr>';
        }
        $html_table .= '</tbody>';
    
        return $html_table;
    }
    
    /**
     * 请求
     *
     * @access public
     * @param string
     * @return
     */
    public static function curl($url, $post = '')
    {
        $header = array(
            'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0'
        );
         
        $ch = curl_init();
         
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
         
        if(strpos($url, 'https') !== FALSE){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
         
        if($post){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
         
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);

        if($error != 0){
            return '';
        }
  
        return $data;
    }
}