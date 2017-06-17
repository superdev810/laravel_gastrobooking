<?php
namespace App\Http\Controllers\WebService;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebService\ServiceHelpers;
use App\Repositories\WebServiceRepository;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

/**
 * Class WebServiceController
 *
 * @package App\Http\Controllers\WebService
 */
class WebWidgetController extends Controller
{
    use Helpers;

    public function css(Request $request) {
        $selectors = [
            'menutype' => '.widget__menutype',
            'menugroup' => '.widget__menugroup',
            'menuofdaybutton' => '.widget__menuofdaybutton',
            'menusubgroup' => '.widget__menusubgroup',
            'menutitle' => '.widget__menutitle',
            'menulist' => '.widget__menulist',
            'menulistcomment' => '.widget__menulistcomment',
            'bookbutton' => '.widget__bookbutton',
            'datepicker' => '.widget__datepicker',
            'menulistorder' => '.widget__menulistorder',
            'menulist_padding_size'=>'.widget__menutitle',//menu padding system
            'headerchange'=>'.widget__headerchange',//header change

            'bgcolor' => ['html, body', 'background-color'],
            'showphoto' => ['.widget__showphoto', 'display', ['no' => 'none', 'yes' => 'block']],
            'menusubgroup_activation' => ['.widget__menusubgroup','display',['off'=>'none','on'=>'block']],//subgroup activation
            'menulist_prefix_one' => ['.widget__prefix_one','display',['off'=>'none','on'=>'block']],//prefix 1 on/off 
            'menulist_prefix_two' => ['.widget__prefix_two','display',['off'=>'none','on'=>'block']],//prefix 2 on/off
            'menutitle_rectangle' => ['.widget__menutitle','margin-bottom',['off'=>'0px','on'=>'20px']],//padding size for list
            'show_order' => ['.widget__order','display',['off'=>'none','on'=>'block']] //show/hide order window
            
        ];

        $webWidgetCSS = new ServiceHelpers\WebWidgetCssHelper();

        $css = $webWidgetCSS
            ->setSelectors($selectors)
            ->generate($_GET)
            ->getSelectors()
            ->get();

        return response($css)->withHeaders(['Content-Type' => 'text/css']);
    }
    
    public function js(Request $request) {
        $scripts = [
            /*     'dropwindow' => "if('on' == \"@{dropup}\")
                 
                  {
                  
                  jQuery('.widget__menutype').each(function() 
                      {
                          if(jQuery(this).next('.list-group-child').hasClass('in')) 
                          {
                              jQuery(this).trigger('click')
                          }
                      } 
                  )
                  }
                  else
                  {
                  jQuery('.widget__menutype').each(function() 
                      {
                          if(!jQuery(this).next('.list-group-child').hasClass('in')) 
                          {
                              jQuery(this).trigger('click')
                          }
                      }
                      )
                  }"
      */

        ];

        $webWidgetJS = new ServiceHelpers\JSGenerator();

        $js = $webWidgetJS
            ->setScripts($scripts)
            ->generate($_GET)
            ->get();

        return response($js)->withHeaders(['Content-Type' => 'application/javascript']);
    }
}