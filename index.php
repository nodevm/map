
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Google Maps</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAPDUET0Qt7p2VcSk6JNU1sBSM5jMcmVqUpI7aqV44cW1cEECiThQYkcZUPRJn9vy_TWxWvuLoOfSFBw" type="text/javascript"></script>
    <script src="epoly.js" type="text/javascript"></script>
  </head>
  <body onunload="GUnload()">
    
    <div id="controls">
     <form onsubmit="start();return false" action="#">
      Enter start and end addresses.<br />
      <input type="text" size="80" maxlength="200" id="startpoint" value="Briarcrest Rd, 90046" /><br />
      <input type="text" size="80" maxlength="200" id="endpoint" value="Hollywood Hills Rd, 90046@34.11327,-118.39089" /><br />
      <input type="submit" value="Start"  />
     </form>
    </div>

    <div id="map" style="width: 700px; height: 500px"></div>
    <div id="step">&nbsp;</div>
    <div id="distance">Miles: 0.00</div>

    <script type="text/javascript">
    //<![CDATA[
    if (GBrowserIsCompatible()) {
 
      var map = new GMap2(document.getElementById("map"));
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng(0,0),2);
      var dirn = new GDirections();
      var step = 5; // metres
      var tick = 100; // milliseconds
      var poly;
      var poly2;
      var lastVertex = 0;
      var eol;
      var car = new GIcon();
          car.image="caricon.png"
          car.iconSize=new GSize(32,18);
          car.iconAnchor=new GPoint(16,9);
      var marker;
      var k=0;
      var stepnum=0;
      var speed = "";   

      function updatePoly(d) {
        // Spawn a new polyline every 20 vertices, because updating a 100-vertex poly is too slow
        if (poly2.getVertexCount() > 20) {
          poly2=new GPolyline([poly.getVertex(lastVertex-1)]);
          map.addOverlay(poly2)
        }

        if (poly.GetIndexAtDistance(d) < lastVertex+2) {
           if (poly2.getVertexCount()>1) {
             poly2.deleteVertex(poly2.getVertexCount()-1)
           }
           poly2.insertVertex(poly2.getVertexCount(),poly.GetPointAtDistance(d));
        } else {
          poly2.insertVertex(poly2.getVertexCount(),poly.getVertex(lastVertex++));
        }
      }

      function animate(d) {
        if (d>eol) {
          document.getElementById("step").innerHTML = "<b>Trip completed<\/b>";
          document.getElementById("distance").innerHTML =  "Miles: "+(d/1609.344).toFixed(2);
          return;
        }
        var p = poly.GetPointAtDistance(d);
        if (k++>=180/step) {
          map.panTo(p);
          k=0;
        }
        marker.setPoint(p);
        document.getElementById("distance").innerHTML =  "Miles: "+(d/1609.344).toFixed(2)+speed;
        if (stepnum+1 < dirn.getRoute(0).getNumSteps()) {
          if (dirn.getRoute(0).getStep(stepnum).getPolylineIndex() < poly.GetIndexAtDistance(d)) {
            stepnum++;
            var steptext = dirn.getRoute(0).getStep(stepnum).getDescriptionHtml();
            document.getElementById("step").innerHTML = "<b>Next:<\/b> "+steptext;
            var stepdist = dirn.getRoute(0).getStep(stepnum-1).getDistance().meters;
            var steptime = dirn.getRoute(0).getStep(stepnum-1).getDuration().seconds;
            var stepspeed = ((stepdist/steptime) * 2.24).toFixed(0);
            step = stepspeed/2.5;
            speed = "<br>Current speed: " + stepspeed +" mph";
          }
        } else {
          if (dirn.getRoute(0).getStep(stepnum).getPolylineIndex() < poly.GetIndexAtDistance(d)) {
            document.getElementById("step").innerHTML = "<b>Next: Arrive at your destination<\/b>";
          }
        }
        updatePoly(d);
        setTimeout("animate("+(d+step)+")", tick);
      }

      GEvent.addListener(dirn,"load", function() {
        document.getElementById("controls").style.display="none";
        poly=dirn.getPolyline();
        eol=poly.Distance();
        map.setCenter(poly.getVertex(0),17);
        map.addOverlay(new GMarker(poly.getVertex(0),G_START_ICON));
        map.addOverlay(new GMarker(poly.getVertex(poly.getVertexCount()-1),G_END_ICON));
        marker = new GMarker(poly.getVertex(0),{icon:car});
        map.addOverlay(marker);
        var steptext = dirn.getRoute(0).getStep(stepnum).getDescriptionHtml();
        document.getElementById("step").innerHTML = steptext;
        poly2 = new GPolyline([poly.getVertex(0)]);
        map.addOverlay(poly2);
        setTimeout("animate(0)",2000);  // Allow time for the initial map display
      });

      GEvent.addListener(dirn,"error", function() {
        alert("Location(s) not recognised. Code: "+dirn.getStatus().code);
      });

      function start() {
        var startpoint = document.getElementById("startpoint").value;
        var endpoint = document.getElementById("endpoint").value;
        dirn.loadFromWaypoints([startpoint,endpoint],{getPolyline:true,getSteps:true});
      }

    }

    // This Javascript is based on code provided by the
    // Community Church Javascript Team
    // http://www.bisphamchurch.org.uk/   
    // http://econym.org.uk/gmap/

    //]]>
    </script>
  </body>

</html>




