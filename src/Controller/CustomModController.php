<?php
namespace Drupal\my_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use \Symfony\Component\HttpFoundation\Response;
use Drupal\webform\Entity;
use Drupal\webform\Entity\WebformSubmission;

class CustomModController extends ControllerBase{
    
    public function import_webform_csv(){
        
        $filePath = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
        $filename = $filePath.'/rsvp_form.csv';
        $handle = fopen($filename, 'r');
        //kint($handle);
        $arrSubmission = array();
        while (($rowData = fgetcsv($handle)) !== FALSE) {
            //kint($rowData);
            $response = $this->add_rsvp_submission($rowData);
            //kint($response);
            break;
        }
        fclose($handle);

        $response = $response;
        return new Response(render($response));
    
    }
    
    public function add_rsvp_submission($rowData = array()){
        
        foreach($rowData as $data){
            $wedformID[] = $data;
        }
        //add to webform and send edm
        $responseWF = false;
        $response = array();
        $values = null;
        //set the values
        $values = [
            'webform_id' => $wedformID[0],
            'entity_type' => $wedformID[1],
            'entity_id' => $wedformID[2],
            'in_draft' => $wedformID[3],
            'uid' => $wedformID[4],
            'langcode' => $wedformID[5],
            'token' => $wedformID[6],
            'uri' => '/webform/rsvp_form/api',
            'remote_addr' => '',
            'data' => [
              'name' => $wedformID[7],
              'email' => $wedformID[8],
              'contact_no' => $wedformID[9],
              'terms_and_conditions' => ($wedformID[10] == 'Yes') ? 1 : 0,
            ],
        ];
        
        // Check webform is open.
        $webform = Webform::load($values['webform_id']);
        //kint($webform);
        if($webform != null){
            //check the webform is open to submit
            $is_open = WebformSubmissionForm::isOpen($webform);
            
            if ($is_open === TRUE) {
              // Validate submission.
                $errors = WebformSubmissionForm::validateValues($values);
                
                // Check there are no validation errors.
                if (!empty($errors)) {
                    //kint($values);
                    //kint($errors);
                    $responseWF = false;
                }
                else {
                    // Submit values and get submission ID.
                    $webform_submission = WebformSubmissionForm::submitValues($values);
                    //kint($webform_submission);
                    if(is_numeric($webform_submission->id()) &&  $webform_submission->id() > 0){
                        $responseWF = true;
                        $response["response"] = "success";
                    }
                }
            }
        }
        
        if($responseWF === false){  
            $response["response"] = "Submission failed. Please contact the site administrator.";
        }
        unset($wedformID);
        return $response;
    }
}












