<?php 
namespace App\Presenters;
function remove_duplicate_models( $cars ) {
    $models = array_map( function( $car ) {
        return $car->policy_id;
    }, $cars );

    $unique_models = array_unique( $models );

    return array_values( array_intersect_key( $cars, $unique_models ) );
}

class RenewalRepresenter{


	static function for_reports(array $renewals){
		$renewals = remove_duplicate_models($renewals);
		usort($renewals,function($a,$b){return strcmp($a->period,$b->period);});
		$result=[];
		try{
			foreach($renewals as $renewal){
				$result[]=[
					'client'=>$renewal->policy->client->first_name,
					'plan'=>$renewal->policy->plan->name,
					'company'=>$renewal->policy->plan->company->name,
					'renovation_date'=>$renewal->created_at->format('Y-m-d'),
					'period'=>$renewal->period,
					'premium'=>$renewal->premium
				];

			}
		}
		catch(\Exception $e){
			echo $e->getMessage();
			print_r($renewal);
			die();
		}
		return $result;
	}
}


?>