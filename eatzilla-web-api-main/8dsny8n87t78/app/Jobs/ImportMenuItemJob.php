<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Model\ImportFoodItem;
use App\Model\Category;
use App\Model\Restaurantcuisines;
use App\Model\Foodlist;
use App\Model\FoodListAvailability;
use App\Model\Choice_category;
use App\Model\Choice;
use Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use App\Model\ZipImageFile;

class ImportMenuItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $itemId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            info("jobs");
            $getAdminFoodImportBaseurl = config('app.admin_food_import_base_url').'/uploads/';
            $foodId = $this->itemId;
            $alreadyImportedItem = ImportFoodItem::whereIn('id',$foodId)->get();
            $validationFailed = 0;
            if(!empty($alreadyImportedItem)){
                foreach($alreadyImportedItem as $item){
                    info($item);
                    $newlyAddedItemId = "";
                    $newlyAddedChoiceCatgeoryId = "";
                    $validationFailed = 0;
                    $categoryName = (!empty($item->category_name))? explode(',',$item->category_name):[];
                    $categoryIdArray = array();
                    if(!empty($categoryName) && count($categoryName)!=0){
                        foreach($categoryName as $category){    
                         // $categoryId = Category::where('restaurant_id',$item->restaurant_id)->whereRaw('lower(category_name) like (?)',["%{$category}%"])->value('id'); 
                            $categoryId = Category::where('restaurant_id', $item->restaurant_id)->whereRaw('LOWER(category_name) = ?', [strtolower($category)])->value('id');                       
                            if(!empty($categoryId)){
                                $categoryIdArray[] = (string)$categoryId;
                            }else{
                                $newCategory = new Category();
                                $newCategory->restaurant_id = $item->restaurant_id;
                                $newCategory->category_name = $category;
                                $newCategory->status = 1;
                                $newCategory->save();
                                $categoryIdArray[] = (string)$newCategory->id;
                            }                        
                        }
                    }else{
                        $validationFailed++;  
                    }
                    $cuisineId = "";
                    if(!empty($item->cuisines)){
                        $cuisineData = Restaurantcuisines::join('cuisines','cuisines.id','=','restaurant_cuisines.cuisine_id')->select('cuisines.name','cuisines.id','restaurant_cuisines.restaurant_id')->where('restaurant_cuisines.restaurant_id',$item->restaurant_id)->whereRaw('lower(name) like (?)',["%{$item->cuisines}%"])->first(); 
                        info($cuisineData);                   
                        if(!empty($cuisineData)){
                            $cuisineId = $cuisineData->id;
                        }else{
                            $validationFailed++;  
                        }                    
                    }else{
                        $validationFailed++;  
                    }  
                    if(!empty($item->food_type)){
                        $veg = array('veg','Veg','VEG');
                        $nonVeg = array('Non veg','non-veg','non veg','Non Veg','NON VEG');
                        $foodType = (in_array($item->food_type, $veg)==1)?1:((in_array($item->food_type, $nonVeg))?0:"");
                        if(!in_array($foodType,[0,1])){
                            $validationFailed++;  
                        }
                    }else{
                        $validationFailed++;
                    }
                    if($validationFailed != 0){
                        ImportFoodItem::where('id',$item->id)->update(['status'=>0]);
                        continue;
                    }
                    $imageLocation = $getAdminFoodImportBaseurl . $item->food_image_name;
                    info($imageLocation);
                    $client = new Client();
                    try {
                        if (@file_get_contents($imageLocation)) {
                            $response = $client->get($imageLocation);
                            if ($response->getStatusCode() === 200) {
                            $contentType = $response->getHeaderLine('content-type');
                            $validImageMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                                if (in_array($contentType, $validImageMimeTypes)) {
                                $file = $response->getBody()->getContents();
                                // Determine the content type based on the response headers
                                $contentType = $response->getHeaderLine('content-type');
                                $filePath = "uploads/food_image/" . $item->food_image;
                                // Upload the image to S3
                                $filetype = Storage::disk('spaces')->put($filePath, $file, 'public', [
                                'ContentType' => $contentType, // Set the content type
                                ]);
                            
                                // Check if the upload was successful
                                    if ($filetype !== false) {
                                    // Image uploaded successfully
                                    info('Image uploaded to S3: ' . $filetype);
                                    // Get the URL of the uploaded image in S3
                                    info($item->id);
                                    $zip = ZipImageFile::where('import_food_list_id',$item->id)->update(['status'=>1]);
                                    info($zip);
                                    $imageUrl = Storage::disk('spaces')->url($filePath);
                                    
                                    // Now, you can use $imageUrl to view the image
                                    info('Image URL: ' . $imageUrl);
                                    } else {
                                    // Handle upload failure
                                    $filePath = '';
                                    info('Image upload to S3 failed.');
                                    }
                                }else{
                                $filePath = '';
                                info('File is not a image.');
                                }
                            } else {
                            $filePath = '';
                            info('Failed to download image from URL.');
                            }
                        } else {
                            $filePath = '';
                            info("File does not exist.");
                        }  
                    } catch (Exception $e) {
                    $filePath = '';
                    info('Error: ' . $e->getMessage());
                    }
                    $sellingDays = !empty($item->selling_days)?explode(',',strtolower($item->selling_days)):[];
                    $sellingDaysStartTime = !empty($item->selling_days)?explode(',',strtolower($item->selling_days_start_time)):[];
                    $sellingDaysFinishTime = !empty($item->selling_days)?explode(',',strtolower($item->selling_days_finish_time)):[];
                    $choiceCategoryName = !empty($item->choice_category_name)?$item->choice_category_name:"";
                    $min = !empty($item->choice_category_name)?$item->min:"";
                    $max = !empty($item->choice_category_name)?$item->max:"";
                    $choiceTitle = !empty($item->choice_category_name)?(!empty($item->choice_title)?explode(',',strtolower($item->choice_title)):[]):[];
                    $choicePrice = !empty($item->choice_category_name)?((!empty($item->choice_title) && !empty($item->choice_price))?explode(',',strtolower($item->choice_price)):[]):[];
                    $newItem = new Foodlist();
                    $newItem->restaurant_id = $item->restaurant_id;
                    $newItem->category_id = json_encode($categoryIdArray);
                    $newItem->menu_id = 1;
                    $newItem->name = $item->food_title;
                    $newItem->price = $item->food_price;
                    $newItem->image = $filePath;
                    $newItem->description = $item->food_description;
                    $newItem->food_preparation_time = $item->food_prepartion_time;
                    $newItem->bulk_cut_off = $item->bc;
                    $newItem->bulk_quantity_set = $item->bs;
                    $newItem->cuisine_id = $cuisineId;
                    $newItem->status = 1;
                    $newItem->tax = 0;
                    $newItem->packaging_charge = $item->packaging_charge;
                    $newItem->is_veg = $foodType;
                    $newItem->is_special = 0;
                    $newItem->is_imported_image = !empty($item->food_image)?1:0;
                    $newItem->save();
                    $newlyAddedItemId = $newItem->id;
                    // food availablity timing 
                    if(!empty($sellingDays) && count($sellingDays)!=0){
                        for($i=0;$i<count($sellingDays);$i++){
                            switch($sellingDays[$i]){
                                case 'monday':
                                case 'mon day':
                                case 'mon':
                                case 'Monday':
                                case 'MONDAY':                                    
                                    $sellingDays[$i] = 'mon';
                                break;
                                case 'tuesday':
                                case 'tues day':
                                case 'tue':
                                case 'Tuesday':
                                case 'TUESDAY':
                                    $sellingDays[$i] = 'tue';
                                break;
                                case 'wednesday':
                                case 'wednes day':
                                case 'wed':
                                case 'Wednesday':
                                case 'WEDNESDAY':
                                    $sellingDays[$i] = 'wed';
                                break;
                                case 'thursday':
                                case 'thurs day':
                                case 'thu':
                                case 'Thursday':
                                case 'THURSDAY':
                                    $sellingDays[$i] = 'thu';
                                break;
                                case 'friday':
                                case 'fri day':
                                case 'fri':
                                case 'Friday':
                                case 'FRIDAY':
                                    $sellingDays[$i] = 'fri';
                                break;
                                case 'saturday':
                                case 'satur day':
                                case 'sat':
                                case 'Saturday':
                                case 'SATURDAY':
                                    $sellingDays[$i] = 'sat';
                                break;
                                case 'sunday':
                                case 'sun day':
                                case 'sun':
                                case 'Sunday':
                                case 'SUNDAY':
                                    $sellingDays[$i] = 'sun';
                                break;
                                case 'alldays':
                                case 'all days':
                                case 'all':
                                case 'Alldays':
                                case 'ALLDAYS':
                                case 'Allday':
                                case 'ALLDAY':                                    
                                    $sellingDays[$i] = 'allday';
                                break;
                                default:
                                    $sellingDays[$i] = $sellingDays[$i];
                                break;
                            }
                            $availablity = new FoodListAvailability();
                            $availablity->food_list_id = $newlyAddedItemId;
                            $availablity->item_days = $sellingDays[$i];
                            $availablity->item_start_time = !empty($sellingDaysStartTime[$i])?$sellingDaysStartTime[$i]:"00:00";
                            $availablity->item_finish_time = !empty($sellingDaysFinishTime[$i])?$sellingDaysFinishTime[$i]:"00:00";
                            $availablity->save();
                        }                    
                    }
                    //choice for food 
                    if (!empty($choiceCategoryName)) {
                        $newChoiceCategory = new Choice_category();
                        $newChoiceCategory->restaurant_id = $item->restaurant_id;
                        $newChoiceCategory->food_id = $newlyAddedItemId;
                        $newChoiceCategory->name = $choiceCategoryName;
                        $newChoiceCategory->min = $min; 
                        $newChoiceCategory->max = $max;
                        $newChoiceCategory->save();
                        $newlyAddedChoiceCatgeoryId = "";
                        $newlyAddedChoiceCatgeoryId = $newChoiceCategory->id;
                        if(!empty($choiceTitle) && count($choiceTitle)!=0){
                            for($r=0;$r<count($choiceTitle);$r++){
                                $newlyAddedChoice = new Choice();
                                $newlyAddedChoice->choice_category_id = $newlyAddedChoiceCatgeoryId;
                                $newlyAddedChoice->name = $choiceTitle[$r];
                                $newlyAddedChoice->price = $choicePrice[$r];
                                $newlyAddedChoice->save();
                            }                        
                        }   
                    }
                    ImportFoodItem::where('id',$item->id)->update(['status'=>1]);
                }
            }  
        }catch(\Exception $e)
        {
            Log::error('import menu item:'.$e);            
        }      
    }
}
