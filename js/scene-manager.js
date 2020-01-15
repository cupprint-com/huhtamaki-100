/**
 * Title: 3D design rendering 
 * Author: David Kelly<support@cupprint.com>
 * Description: 
 */

/**
 * Initialises a 3D scene using design collateral created using the online design workspace.
 * $address String,address of API used for collateral
 * $image String, URL of the image used for the design
 * $scene String, Babylon scene used for 3D effect
 * $target String, ID of the canvas used to render the scene
 */
function renderDesignResult($,$address,$image,$scene,$target){
	// 'global' scene object
	var _scene;
	// preload the image used on scene
	$.image = new Image();
	$.image.src = $image;
	// once the image is loaded initialise canvas elements
	$.image.onload = function(){
	
			  // 'model' is used to wrap babylon object
			  var model=function(){
			  	// override reference to "this" /
			      var _this = this;
			      var canvas=document.getElementById($target);
			  	// load the 3D engine
			      _this.engine = new BABYLON.Engine(canvas, true);
			      _this.engine.displayLoadingUI();
			      BABYLON.SceneLoader.ShowLoadingScreen = true;
			      //BABYLON.SceneLoader.ShowLoadingScreen = false;
			   // Load scene
			      BABYLON.SceneLoader.Load($address, $scene, _this.engine, function(scene) {
			          _this.scene = scene;
			  		 window.scene = scene;
			          _scene = scene;
			          
			       // initialise configuration properties
			          _this.modelProperties();

			          scene.executeWhenReady(function() {
			        	 // performs minor animation of the scene
			        	  AnimateDesign();
			          	_this.engine.runRenderLoop(function () {
			          		_this.scene.render();
			              });
			          	  
			          	/** scenedata defines the image to be rendered on 3D model */
					    //var scenedata =settings.scenedata;
						//for (var j in scenedata.texture){
							//var fileName=scenedata.image[scenedata.texture[j][1]-1];
							$.image = new Image();
							$.image.src = $image; 
							/** when image is loaded add the textures */
							$.image.onload = function(){
								var _rand = Math.random();
								var _time = Date.now();

								
									var x  = new BABYLON.Texture('data:image_'+_rand+'_'+_time+'jpeg', _scene, undefined, undefined, undefined,function(){
										window.scene.meshes[0].material.diffuseTexture = x;
									},undefined, $image); 
								
								

							}		
						//}

							          	
			          });
			      });  
			      window.addEventListener("resize", function() {
			          _this.engine.resize();
			      });
			  };			  
			  
			  model.prototype.modelProperties = function() {
					this.scene.autoClear=false;
					// set transparent background
				    this.scene.clearColor = new BABYLON.Color4(0,0,0,0);
				    //Define the camera
				    this.cameraProperties();
					// lighting
				    this.lightingProperties();
				    // materials
				    this.materialProperties();
				 
				};

				/** Active Camera Properties */
				model.prototype.cameraProperties = function(){
					// (x, y, z) https://doc.babylonjs.com/classes/2.5/vector3
					var x=-22.50829158363229;
					var y= 14.027938391782204;
					var z= 4.591708251598788;
					

					var cameraVector=new BABYLON.Vector3(0, 3, 0);
					//var cameraVector=new BABYLON.Vector3(x,y,z);
					// args: (name, alpha, beta, radius, target, scene) https://doc.babylonjs.com/classes/2.5/arcrotatecamera
					var arcCamera = new BABYLON.ArcRotateCamera("ArcRotateCamera", 10, 0, 100, cameraVector , this.scene);
					// use default scene camera position
				   // arcCamera.setPosition(this.scene.activeCamera.position);
					this.scene.activeCamera.position.x=x;
					this.scene.activeCamera.position.y=y;
					this.scene.activeCamera.position.z=z;
					//new BABYLON.Vector3(0, 0, 20)
					arcCamera.setPosition(this.scene.activeCamera.position);
				    arcCamera.lowerRadiusLimit = 13;
				    arcCamera.attachControl(this.engine.getRenderingCanvas(),true);
				    this.scene.activeCamera = arcCamera;	
				    
				};

				/** Lighting */
				model.prototype.lightingProperties = function() {
					// (x, y, z) https://doc.babylonjs.com/classes/2.5/vector3
					var lightSourceVector=new BABYLON.Vector3(0.2,1,0);
				    this.scene.lights.forEach(function(l) {
				        l.intensity = 0.3
				        
				    });
				    
				    var h = new BABYLON.HemisphericLight("hemi", lightSourceVector, this.scene);
				    h.intensity = 1.55;
				};

				/** Scene materials */
				model.prototype.materialProperties = function() {
				    _scene = this.scene;
				    this.scene.meshes.forEach(function (mesh) {
				    	
				        if (mesh.material) {
				            if (mesh.material.name == "white") {
				                mesh.material.backFaceCulling = false;
				                mesh.material.diffuseColor = new BABYLON.Color3(0.85,0.85,0.85);
				            }
				        }
				    });
				};
				
				
				
			  
			    new model();
	
	} // function to load image					  
	
	
	
	
	
}





var DesignArcAnimation = function (fromAlpha, toAlpha, fromBeta,toBeta,fromRadius, toRadius) {
	var scene = window.scene;
	var camera = scene.activeCamera;
    var animCamAlpha = new BABYLON.Animation("animCam", "alpha", 30,
                              BABYLON.Animation.ANIMATIONTYPE_FLOAT,
                              BABYLON.Animation.ANIMATIONLOOPMODE_RELATIVE);

    var keysAlpha = [];
    keysAlpha.push({
        frame: 0,
        value: fromAlpha
    });
    keysAlpha.push({
        frame: 100,
        value: toAlpha
    });
    
    
    var animCamBeta = new BABYLON.Animation("animCam", "beta", 30,
                             BABYLON.Animation.ANIMATIONTYPE_FLOAT,
                             BABYLON.Animation.ANIMATIONLOOPMODE_CONSTANT);

    var keysBeta = [];
    keysBeta.push({
        frame: 0,
        value: fromBeta
    });
    keysBeta.push({
        frame: 100,
        value: toBeta
    });
    var animCamRadius = new BABYLON.Animation("animCam", "radius", 30,
                            BABYLON.Animation.ANIMATIONTYPE_FLOAT,
                            BABYLON.Animation.ANIMATIONLOOPMODE_CONSTANT);

    var keysRadius = [];
    keysRadius.push({
        frame: 0,
        value: fromRadius
    });
    keysRadius.push({
        frame: 100,
        value: toRadius
    });
    
    
    animCamAlpha.setKeys(keysAlpha);
    animCamBeta.setKeys(keysBeta);
    animCamRadius.setKeys(keysRadius);
    
    camera.animations.push(animCamAlpha);
    camera.animations.push(animCamBeta);
    camera.animations.push(animCamRadius);
    scene.beginAnimation(camera, 0, 100, false, 1, function () {
    	console.log(camera.position);
    	
    });

}

var DesignAnimateLighting = function(light,startValue,midValue,endValue){
	var scene = window.scene;
	var animateLighting = new BABYLON.Animation("animateLighting", "intensity", 30,
            BABYLON.Animation.ANIMATIONTYPE_FLOAT,
            BABYLON.Animation.ANIMATIONLOOPMODE_CONSTANT);
	var keys = [];
    keys.push({
        frame: 0,
        value: startValue
    });
    keys.push({
        frame: 80,
        value: midValue
    });
    keys.push({
        frame: 100,
        value: endValue
    });
    animateLighting.setKeys(keys);
    light.animations.push(animateLighting);
    scene.beginAnimation(light, 0, 100, false, 1, function () {
    	
    	
    });
}

/**
 * Animates the rendered scene to provide visual cue that user can manipulate it
 * @param settings json Object
 * @returns
 */
function AnimateDesign(){
	// get reference to the currently rendered scene, camera & light
	var scene = window.scene;
	var camera = scene.activeCamera;
	var light = scene.lights[scene.lights.length-1];
	
	// read target setup from settings, this is used to set the camera position 
	var target_alpha=parseFloat(camera.alpha);
	var target_beta=parseFloat(camera.beta);
	var target_radius=parseFloat(camera.radius );
	var target_intensity=parseFloat(light.intensity);
	var mid_intensity=target_intensity + .3;
	
	
	// the camera position for the scene 
	var targetPosition=new BABYLON.Vector3(target_alpha,target_beta,target_radius);
	camera.setPosition(targetPosition);	
	
	var start_radius = parseFloat(camera.radius  - 7);
	var start_alpha = parseFloat(camera.alpha/20.5);
	var start_beta=parseFloat(camera.beta);
	
	DesignArcAnimation(start_alpha,target_alpha,start_beta, target_beta,start_radius, target_radius);
}



