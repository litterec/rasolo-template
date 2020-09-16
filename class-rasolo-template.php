<?php

//Example.
//--------

/*
// The inial stage
$test_templ=new RasoloTemplate();
$test_templ->add('Tag first {first} Tag second {second} Tag third {third} ');
$test_templ->set('first',' +++The first value itself+++ ');
$test_templ->set('second',' +++The second value itself+++ ');
$test_templ->set('third',' +++The third with the first inside {first}+++ ');


// Now We could have such incomplete rendered string
// string(142) "Tag first  +++The first value itself+++  Tag second  +++The second value itself+++  Tag third  +++The third with the first inside {first}+++  "

// To avoid this we have two options
//$test_templ->replaceAllTags('first');
// The result is such as
// string(165) "Tag first  +++The first value itself+++  Tag second  +++The second value itself+++  Tag third  +++The third with the first inside  +++The first value itself+++ +++  "

// or
$test_templ->replaceOneTag('third');
// The result is the same
// string(165) "Tag first  +++The first value itself+++  Tag second  +++The second value itself+++  Tag third  +++The third with the first inside  +++The first value itself+++ +++  "

$rendered=$test_templ->get_rendered();
myvar_dump($rendered,'$rendered',1);
myvar_dump($test_templ,'$test_templ',1);
*/

 class RasoloTemplate {

     // Template file
     private $template;
     private $debug_mode;
     private $full_template_path;
     // Tags array
     private $tags = [];
     private $tag_keys = [];
     private $already_subst = [];
     private $default_template = false;

     public function __construct($templateFile=false)
     {
        $this->debug_mode=false;
        $this->full_template_path=dirname( __FILE__ ).'/../template/';
        $this->full_template_path=realpath($this->full_template_path).'/';
//         myvar_dump($some_path);
//         myvar_dd($this);

         if($templateFile){
             if(file_exists($this->full_template_path.self::get_tpl_fn($templateFile))){
                 $this->template = $this->getFile(self::get_tpl_fn($templateFile));
             } else {
                 $this->template = '';
//                 $this->template = $this->getFile('main.tpl');
                 return $this;
             }

             // If the template file is not accessible
             if(!$this->template) {
                  echo 'Location: '.get_root_url().'/?dbg=001&msg=unknown_tmpl&tmpl='.self::get_tpl_fn($templateFile);
//                  header( 'Location: '.get_root_url().'/?dbg=001&msg=unknown_tmpl&tmpl='.self::get_tpl_fn($templateFile), true, 301 );
                  exit;
             }
         } else {
             $this->template='';
         }
         return $this;
     }

     public function add($some_html)
     {
         $this->template = $this->template.$some_html;
         return $this;
     }

     public function add_from_file($templateFile,$tpl_group='main')
     {
         $this->template = $this->template.$this->getFile(self::get_tpl_fn($templateFile),$tpl_group);
         return $this;
     }

     public function push($some_html)
     {
         $this->template = $some_html.$this->template;
        return $this;
     }

     public function push_from_file($templateFile,$tpl_group='main')
     {
         $this->template = $this->getFile(self::get_tpl_fn($templateFile),$tpl_group).$this->template;
        return $this;
     }

     // Get the template file
     public function getFile($file,$tpl_group='main') {

         if('main'==$tpl_group){
            $full_path_file=$this->full_template_path.self::get_tpl_fn($file);
         } else {
             $full_path_file=$this->full_template_path.$tpl_group.'/'.self::get_tpl_fn($file);
         }

//         if(false && 'paints'==substr($file,0,6)){
//             $f_exists=file_exists($full_path_file);
//             myvar_dump($full_path_file,'$full_path_file');
//             myvar_dump($f_exists,'$f_exists_12113');
//         }

         if(file_exists($full_path_file)){
             return file_get_contents($full_path_file);
         } else {

             if($this->default_template){
                if('main'==$tpl_group){
                    $full_path_file=$this->full_template_path.self::get_tpl_fn($this->default_template);
                } else {
                    $full_path_file=$this->full_template_path.$tpl_group.'/'.self::get_tpl_fn($this->default_template);
                }
                 if(file_exists($full_path_file)){
                      return file_get_contents($full_path_file);
                 } else {
                     myvar_dump($full_path_file,'$full_path_file');
                     echo 'Location: '.get_root_url().'/?dbg=002&msg=unknown_tmpl&tmpl='.self::get_tpl_fn($this->default_template);
//                     header( 'Location: '.get_root_url().'/?dbg=002&msg=unknown_tmpl&tmpl='.self::get_tpl_fn($this->default_template), true, 301 );
                     exit;
                 }


             }


             /* *************************** */
             /*                             */
             /*         Temporaryly!        */
             /*                             */
             /* *************************** */

             echo 'Location: '.get_root_url().'/?dbg=003&msg=unknown_tmpl&tmpl='.self::get_tpl_fn($file);
//             header( 'Location: '.get_root_url().'/?dbg=003&msg=unknown_tmpl&tmpl='.self::get_tpl_fn($file), true, 301 );
             exit;
//             die('The template file '.$file.' does not exist in the group '.$tpl_group.' ');
         }
     }

     // Set the {tag} with value
     public function set($tag, $value)
    {
        $this->tags[$tag] = $value;
//        if($this->debug_mode && verify_ip()){
//            myvar_dump($tag,'$set tag 004');
//            myvar_dump(array_keys($this->tags),'$this->tags 004');
//        }

        return $this;
    }

     public function set_from_file($tag, $file,$tpl_group='main')
     {
         $this->tags[$tag] = $this->getFile($file,$tpl_group);

         return $this;
     }

// Replace all existing tags in particular existing memory tag
// The goal - is to substitute all tag names to one particular memory tag
     public function replaceOneTag($onetag)
     {
         if(empty($this->tags[$onetag])){
             return $this;
         }

         foreach ($this->tags as $tag => $value) {
             if($tag==$onetag){
                 continue;
             }
             $this->tags[$onetag] = str_replace('{'.$tag.'}', $value, $this->tags[$onetag]);

//             if($this->debug_mode && verify_ip() && $tag=='breadcrumbs'){
//                 myvar_dump($tag,'$tag 004 ==========');
//                 myvar_dump($onetag,'$onetag 004 ==========');
//                 myvar_dump(array_keys($this->tags),'$this->tags 003');
//                 myvar_dump($this->tags,'$this->tags 003');
//                 myvar_dd($tag,'$onetag 003');
//             }

         }

         return $this;
     }

// Replace some tag in other tags exept itself
// The goal - is to substitute particular tag name to all memory tags
     public function replaceAllTags($one_e_tag) {
         if(empty($this->tags[$one_e_tag])){
             return $this;
         }
         foreach ($this->tags as $tag => $value) {
             if($tag==$one_e_tag){
                 continue;
             }
             $this->tags[$tag] = str_replace('{'.$one_e_tag.'}', $this->tags[$one_e_tag], $this->tags[$tag]);
         }
         return $this;
     }

     private function replaceTags()
     {

//         $cnt_before=array();
//         $cnt_after=array();
         for($ic=1;$ic<=1;$ic++){

//             $cnt_before[]=count($this->tags);
             foreach ($this->tags as $tag => $value) {

                if(in_array($tag,$this->already_subst)){
                     continue;
                }

                if(strpos($this->template,'{'.$tag.'}')){

                    $pattern='/\{(\w{1,10})\}/s';
                    if(!preg_match($pattern, $this->tags[$tag] )){
//                        rasolo_debug_to_file($tag,'newfiles');
                        $this->already_subst[]=$tag;
                        unset($this->tags[$tag]);
                    };

                }

                $this->template = str_replace('{'.$tag.'}', $value, $this->template);

             }

//             $cnt_after[]=count($this->tags);
             if(0==count($this->tags)){
                 break;
             }
         }


         $this->tag_keys=array_keys($this->tags);
         $lure='/\{('.implode('|',$this->tag_keys).')\}/i';
         $tag_matches=array();
         $is_tag=preg_match($lure,$this->template,$tag_matches);
         if(1==$is_tag){
             myvar_dump($tag_matches,'$tag_matches');
             die('some tags have not rendered');
         }

//         $test=$this->template;
//         myvar_dump($test,'$test 323424',0,0,1);
//echo '$is_tag='.$is_tag.'{==';



//         $tags_after=$this->tags;
//         myvar_dump($cnt_before,'$cnt_before',1);
//         myvar_dump($cnt_after,'$cnt_after',1);
//         myvar_dump($ic,'$ic',1);
//         myvar_dump($tags_after,'$tags_after',1);

         return $this;
     }

     // Render the build template
     public function render($echo_mode=true)
     {
        $this->replaceTags();
        if($echo_mode){
            echo $this->template;
        }
        return $this->template;
     }

     public function get_rendered()
     {
         $this->replaceTags();

         return $this->template;
     }

     public function searchFor($what_do_you_search_for,$tpl_group='main'){

        $srch_for=strtolower(trim(mb_substr($what_do_you_search_for,0,100,'utf-8')));
        if('main'==$tpl_group){
            $our_dir=scandir(dirname( __FILE__ ).'/../template/');
        } else {
            $our_dir=scandir(dirname( __FILE__ ).'/../template/'.$tpl_group);
        }
        $found_slugs=array();

        foreach($our_dir as $nth_file){
            if('.'==$nth_file || '..'==$nth_file || 'search.tpl'==$nth_file){
                continue;
            }

            $nth_file_content=$this->getFile($nth_file,$tpl_group);
            $nth_file_content=strip_tags($nth_file_content);
            $nth_file_content=preg_replace('/\{[\s\S]+?\}/', '', $nth_file_content);
            $nth_file_content=strtolower($nth_file_content);
            $slug_arr=explode('.tpl',$nth_file);

            if(strpos($nth_file_content,$srch_for)){
                $found_slugs[]=array_shift($slug_arr);
            }

// if('paints.tpl'==$nth_file){
//$some_file_content=htmlspecialchars($nth_file_content);
//myvar_dump($some_file_content,'$some_file_content');
// }


        };
        if(empty($found_slugs)){
            return false;
        } else {
            return $found_slugs;
        }

     }

     public static function insert_tag($templ,$tagname,$tag_content){
         if(strpos($templ,'{'.$tagname.'}')===false){
             return '<!-- bad template/tag pair for tag '.$tagname.' -->'.chr(10);
         } else {
             return str_replace('{'.$tagname.'}',$tag_content,$templ);
         }
     }

     public function set_default_tpl($dflt){
         $this->default_template=$dflt;
     }
     public static function get_tpl_fn($fn){
         if(strpos($fn,'.')===false){
             return $fn.'.tpl';
         }
         return $fn;
     }
     public function set_debug($mode){
         if(is_bool($mode)){
            $this->debug_mode=$mode;
         }
     }

}