<?php

/*html parser for parsing html in php*/
include('html_parser.php');


class Requests {

      public $totalNumberOfRequests = 0;

      public $totalDownloadSize = 0;

      /* 
        method to get the request of url and print it
        @method($param) @param = $url
      */
      public function getRequests($url){

        try{

            list($totalNumberOfRequests, $totalDownloadSize)  = $this->calculateTotalRequests($url, $this->totalNumberOfRequests, $this->totalDownloadSize);

            echo nl2br("Total Number of HTTP requests: $this->totalNumberOfRequests\nTotal Download size for all requests: $this->totalDownloadSize");

        }catch (Exception $e){
          
            echo 'Sorry Please Try Again Later!';

        }

      }


      /* 
        method to calculate total requests & download size of remote url
        @method($param1, $param2, $param3) 
          @param 1 = $url
          @param 2 = $totalNumberOfRequests
          @param 3 = $totalDownloadSize
      */
      public function calculateTotalRequests($url, $totalNumberOfRequests, $totalDownloadSize){

        try{

           /*If the passed url is not html execute here*/
          if (!$this->checkForHtmlUrl($url))
          {

            $this->totalNumberOfRequests += 1;

            $this->totalDownloadSize = $this->getRemoteFileSize($url);

            return;
          }

          /*Is passed url is html continue here*/
          $html = file_get_html($url);


          /*Checks for all images in html*/
          foreach($html->find('img') as $element)
          {

              $size = $this->getRemoteFileSize($element->src);

                $this->totalNumberOfRequests += 1;
              
                $this->totalDownloadSize = $this->totalDownloadSize + $size;   
                    
          }


          /*Checks for all css in html*/
          foreach($html->find('link') as $element)
          {

            if (strpos($element->href,'.css') !== false) {

              $size = $this->getRemoteFileSize($element->href);

                $this->totalNumberOfRequests += 1;
               
                $this->totalDownloadSize = $this->totalDownloadSize + $size; 
                   
            }
               
          }


          /*Checks for all scripts in html*/
          foreach($html->find('script') as $element)
          {

            if (strpos($element->src,'.js') !== false) {

              $size = $this->getRemoteFileSize($element->src);
                
                $this->totalNumberOfRequests += 1;

                $this->totalDownloadSize = $this->totalDownloadSize + $size;     

            }

          }


          /*Checks for all iframes in html*/
            /*
              As iframe is used to display a web page within a web page.
              we can't count count iframe as single html document, it may contain multiple css,js,images.
              have to make use of recursive function to get total number of requests and download size of iframe
            */
          foreach($html->find('iframe') as $element)
          {
                                                                /*recursive function call*/
            list($totalNumberOfRequests, $totalDownloadSize)  = $this->calculateTotalRequests($element->src, $this->totalNumberOfRequests, $this->totalDownloadSize);
                     
          }

          return array($this->totalNumberOfRequests, $this->totalDownloadSize) ;


        }catch (Exception $e){

          echo 'Error In calculating Requests!';

        }


      }


       /* 
        method to get the remote file size of url
        @method($param) @param = $url
      */
      public function getRemoteFileSize($url) {

        /*Get Headers Approach for finding remote file size*/
        $headers = @get_headers($url, true);
        
        if (isset($headers['Content-Length'])) 
        {
          return $headers['Content-Length'];
        }
        
        if (isset($headers['Content-length'])) 
        {
          return $headers['Content-length'];
        }

        /*cURL Approach for finding remote file size*/
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array('User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1.3) Gecko/20090824 Firefox/3.5.3'),
            ));

        curl_exec($curl);
        
        $size = curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD);
        
        return $size;
            
        curl_close($curl);

      }


       /* 
        method to check if the passed url is html
        @method($param) @param = $url
      */
      public function checkForHtmlUrl($url){

         $curl = curl_init($url);

         curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
         curl_setopt($curl, CURLOPT_HEADER, TRUE);
         curl_setopt($curl, CURLOPT_NOBODY, TRUE);

         $data = curl_exec($curl);
         $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE );

         curl_close($curl);

         /*if content Type is text/html*/
         if (strpos($contentType,'text/html') !== false)
         {
          return TRUE;
         }else{
          return FALSE;
         }
        
      }


  }


    $url =  $argv[1];

    $requests = new Requests();

    $requests->getRequests($url);

?>