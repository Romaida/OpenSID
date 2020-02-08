<style>
  #map
  {
    width:100%;
    height:65vh
  }
  .icon {
    max-width: 70%;
    max-height: 70%;
    margin: 4px;
  }
  .leaflet-control-layers {
  	display: block;
  	position: relative;
  }
  .leaflet-control-locate a {
  font-size: 2em;
	}
</style>
<!-- Menampilkan OpenStreetMap -->
<div class="content-wrapper">
  <section class="content-header">
		<h1>Peta <?= $area['nama']?></h1>
		<ol class="breadcrumb">
			<li><a href="<?= site_url('hom_sid')?>"><i class="fa fa-home"></i> Home</a></li>
			<li><a href="<?= site_url("area")?>"> Pengaturan Area </a></li>
			<li class="active">Peta <?= $area['nama']?></li>
		</ol>
	</section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <form action="<?= $form_action?>" method="POST" enctype="multipart/form-data" class="form-horizontal">
            <div class="box-body">
              <div class="row">
                <div class="col-sm-12">
                  <div id="map">
                    <input type="hidden" id="path" name="path" value="<?= $area['path']?>">
                    <input type="hidden" name="id" id="id"  value="<?= $area['id']?>"/>
                  </div>
                </div>
              </div>
            </div>
            <div class='box-footer'>
              <div class='col-xs-12'>
                <a href="<?= site_url("area")?>" class="btn btn-social btn-flat bg-purple btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block" title="Kembali"><i class="fa fa-arrow-circle-o-left"></i> Kembali</a>
                <a href="#" class="btn btn-social btn-flat btn-success btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block" download="OpenSID.gpx" id="exportGPX"><i class='fa fa-download'></i> Export ke GPX</a>
								<button type='reset' class='btn btn-social btn-flat btn-danger btn-sm' id="resetme"><i class='fa fa-times'></i> Reset</button>
								<button type='submit' class='btn btn-social btn-flat btn-info btn-sm pull-right' id="simpan_kantor"><i class='fa fa-check'></i> Simpan</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
  var infoWindow;
  window.onload = function()
  {
  	<?php if (!empty($desa['lat']) && !empty($desa['lng'])): ?>
  		var posisi = [<?=$desa['lat'].",".$desa['lng']?>];
  		var zoom = <?=$desa['zoom'] ?: 18?>;
  	<?php else: ?>
  		var posisi = [-1.0546279422758742,116.71875000000001];
  		var zoom = 4;
  	<?php endif; ?>

  	//Inisialisasi tampilan peta
  	var peta_area = L.map('map').setView(posisi, zoom);

    //1. Menampilkan overlayLayers Peta Semua Wilayah
    var marker_desa = [];
    var marker_dusun = [];
    var marker_rw = [];
    var marker_rt = [];

    //WILAYAH DESA
    <?php if (!empty($desa['path'])): ?>
    var daerah_desa = <?=$desa['path']?>;
    var daftar_desa = JSON.parse('<?=addslashes(json_encode($desa))?>');
    var jml = daerah_desa[0].length;
    daerah_desa[0].push(daerah_desa[0][0]);
    for (var x = 0; x < jml; x++)
    {
      daerah_desa[0][x].reverse();
    }
    var style_polygon = {
      stroke: true,
      color: '#FF0000',
      opacity: 1,
      weight: 2,
      fillColor: '#8888dd',
      fillOpacity: 0.5
    };
    <?php if (is_file(LOKASI_LOGO_DESA . "favicon.ico")): ?>
    var point_style = {
        iconSize: [32, 37],
        iconAnchor: [16, 37],
        popupAnchor: [0, -28],
        iconUrl: "<?= base_url()?><?= LOKASI_LOGO_DESA?>favicon.ico"
    };
		<?php else: ?>
    var point_style = {
        iconSize: [32, 37],
        iconAnchor: [16, 37],
        popupAnchor: [0, -28],
        iconUrl: "<?= base_url()?>favicon.ico"
    };
		<?php endif; ?>

    marker_desa.push(turf.polygon(daerah_desa, {content: "<?=ucwords($this->setting->sebutan_desa.' '.$desa['nama_desa'])?>", style: style_polygon, style: L.icon(point_style)}))
    marker_desa.push(turf.point([<?=$desa['lng'].",".$desa['lat']?>], {content: "Kantor Desa",style: L.icon(point_style)}));
    <?php endif; ?>

    //WILAYAH DUSUN
    <?php if (!empty($dusun_gis)): ?>
      var dusun_style = {
        stroke: true,
        color: '#FF0000',
        opacity: 1,
        weight: 2,
        fillColor: '#FFFF00',
        fillOpacity: 0.5
      }
      var daftar_dusun = JSON.parse('<?=addslashes(json_encode($dusun_gis))?>');
      var jml = daftar_dusun.length;
      var jml_path;
      for (var x = 0; x < jml;x++)
      {
        if (daftar_dusun[x].path)
        {
          daftar_dusun[x].path = JSON.parse(daftar_dusun[x].path)
          jml_path = daftar_dusun[x].path[0].length;
          for (var y = 0; y < jml_path; y++)
          {
            daftar_dusun[x].path[0][y].reverse()
          }

          daftar_dusun[x].path[0].push(daftar_dusun[x].path[0][0])
          marker_dusun.push(turf.polygon(daftar_dusun[x].path, {content: '<?=ucwords($this->setting->sebutan_dusun)?>' + ' ' + daftar_dusun[x].dusun, style: dusun_style}));
        }
      }
    <?php endif; ?>

    //WILAYAH RW
    <?php if (!empty($rw_gis)): ?>
      var rw_style = {
        stroke: true,
        color: '#FF0000',
        opacity: 1,
        weight: 2,
        fillColor: '#8888dd',
        fillOpacity: 0.5
      }
      var daftar_rw = JSON.parse('<?=addslashes(json_encode($rw_gis))?>');
      var jml = daftar_rw.length;
      var jml_path;
      for (var x = 0; x < jml;x++)
      {
        if (daftar_rw[x].path)
        {
          daftar_rw[x].path = JSON.parse(daftar_rw[x].path)
          jml_path = daftar_rw[x].path[0].length;
          for (var y = 0; y < jml_path; y++)
          {
            daftar_rw[x].path[0][y].reverse()
          }
          daftar_rw[x].path[0].push(daftar_rw[x].path[0][0])
          marker_rw.push(turf.polygon(daftar_rw[x].path, {content: 'RW' + ' ' + daftar_rw[x].rw, style: rw_style}));
        }
      }
    <?php endif; ?>

    //WILAYAH RT
    <?php if (!empty($rt_gis)): ?>
      var rt_style = {
        stroke: true,
        color: '#FF0000',
        opacity: 1,
        weight: 2,
        fillColor: '#008000',
        fillOpacity: 0.5,
      }
      var daftar_rt = JSON.parse('<?=addslashes(json_encode($rt_gis))?>');
      var jml = daftar_rt.length;
      var jml_path;
      for (var x = 0; x < jml;x++)
      {
        if (daftar_rt[x].path)
        {
          daftar_rt[x].path = JSON.parse(daftar_rt[x].path)
          jml_path = daftar_rt[x].path[0].length;
          for (var y = 0; y < jml_path; y++)
          {
            daftar_rt[x].path[0][y].reverse()
          }
          daftar_rt[x].path[0].push(daftar_rt[x].path[0][0])
          marker_rt.push(turf.polygon(daftar_rt[x].path, {content: 'RT' + ' ' + daftar_rt[x].rt, style: rt_style}));
        }
      }
    <?php endif; ?>

    //2. Menampilkan overlayLayers Peta Semua Wilayah
    <?php if (!empty($wil_atas['path'])): ?>
      var overlayLayers = overlayWil(marker_desa, marker_dusun, marker_rw, marker_rt);
    <?php else: ?>
      var overlayLayers = {};
    <?php endif; ?>

    //Menampilkan BaseLayers Peta
    var baseLayers = getBaseLayers(peta_area, '<?=$this->setting->google_key?>');

    //Menampilkan Peta wilayah yg sudah ada
    <?php if (!empty($area['path'])): ?>
      var daerah_wilayah = <?=$area['path']?>;

      //Titik awal dan titik akhir poligon harus sama
      daerah_wilayah[0].push(daerah_wilayah[0][0]);

      var poligon_wilayah = L.polygon(daerah_wilayah).addTo(peta_area);
      poligon_wilayah.on('pm:edit', function(e)
      {
        document.getElementById('path').value = getLatLong('Poly', e.target).toString();
      })

      var layer = poligon_wilayah;
      var geojson = layer.toGeoJSON();
      var shape_for_db = JSON.stringify(geojson);
      var gpxData = togpx(JSON.parse(shape_for_db));

      $("#exportGPX").on('click', function (event) {
        data = 'data:text/xml;charset=utf-8,' + encodeURIComponent(gpxData);
        $(this).attr({
          'href': data,
          'target': '_blank'
        });
      });

      peta_area.panTo(poligon_wilayah.getBounds().getCenter());

      // set value setelah create polygon
      document.getElementById('path').value = getLatLong('Poly', layer).toString();

    <?php endif; ?>

    //Tombol yang akan dimunculkan di peta
    var options =
    {
      position: 'topright', // toolbar position, options are 'topleft', 'topright', 'bottomleft', 'bottomright'
      drawMarker: false, // adds button to draw markers
      drawCircleMarker: false, // adds button to draw markers
      drawPolyline: false, // adds button to draw a polyline
      drawRectangle: false, // adds button to draw a rectangle
      drawPolygon: true, // adds button to draw a polygon
      drawCircle: false, // adds button to draw a cricle
      cutPolygon: false, // adds button to cut a hole in a polygon
      editMode: true, // adds button to toggle edit mode for all layers
      removalMode: true, // adds a button to remove layers
    };

    //Menambahkan zoom scale ke peta
    L.control.scale().addTo(peta_area);

    //Menambahkan toolbar ke peta
    peta_area.pm.addControls(options);

    //Menambahkan Peta wilayah
    peta_area.on('pm:create', function(e)
    {
      var type = e.layerType;
      var layer = e.layer;
      var latLngs;

      if (type === 'circle') {
        latLngs = layer.getLatLng();
      }
      else
      latLngs = layer.getLatLngs();

      var p = latLngs;
      var polygon = L.polygon(p, { color: '#A9AAAA', weight: 4, opacity: 1 }).addTo(peta_area);

      polygon.on('pm:edit', function(e)
      {
        document.getElementById('path').value = getLatLong('Poly', e.target).toString();
      });

      peta_area.fitBounds(polygon.getBounds());

      // set value setelah create polygon
      document.getElementById('path').value = getLatLong('Poly', layer).toString();
    });

    //Unggah Peta dari file GPX/KML
    var style = {
      color: 'red',
      opacity: 1.0,
      fillOpacity: 1.0,
      weight: 2,
      clickable: true
    };

    L.Control.FileLayerLoad.LABEL = '<img class="icon" src="<?= base_url()?>assets/images/folder.svg" alt="file icon"/>';

    control = L.Control.fileLayerLoad({
      addToMap: false,
      formats: [
        '.gpx',
        '.geojson'
      ],
      fitBounds: true,
      layerOptions: {
        style: style,
        pointToLayer: function (data, latlng) {
          return L.circleMarker(
            latlng,
            { style: style }
          );
        },

      }
    });
    control.addTo(peta_area);

    control.loader.on('data:loaded', function (e) {
      var type = e.layerType;
      var layer = e.layer;
      var coords=[];
      var geojson = layer.toGeoJSON();
      var options = {tolerance: 0.0001, highQuality: false};
      var simplified = turf.simplify(geojson, options);
      var shape_for_db = JSON.stringify(geojson);
      var gpxData = togpx(JSON.parse(shape_for_db));

      $("#exportGPX").on('click', function (event) {
        data = 'data:text/xml;charset=utf-8,' + encodeURIComponent(gpxData);

        $(this).attr({
          'href': data,
          'target': '_blank'
        });

      });

      var polygon =
      //L.geoJson(JSON.parse(shape_for_db), { //jika ingin koordinat tidak dipotong/simplified
      L.geoJson(simplified, {
        pointToLayer: function (feature, latlng) {
          return L.circleMarker(latlng, { style: style });
        },
        onEachFeature: function (feature, layer) {
          coords.push(feature.geometry.coordinates);
        },

      }).addTo(peta_area);

      var jml = coords[0].length;
      coords[0].push(coords[0][0]);
      for (var x = 0; x < jml; x++)
      {
        coords[0][x].reverse();
      }

      polygon.on('pm:edit', function(e)
      {
        document.getElementById('path').value = JSON.stringify(coords);
      });

      document.getElementById('path').value = JSON.stringify(coords);
      peta_area.fitBounds(polygon.getBounds());

      // set value setelah create polygon
      document.getElementById('path').value = getLatLong('Poly', layer).toString();
      document.getElementById('zoom').value = peta_area.getZoom();
    });

    //Geolocation IP Route/GPS
  	var lc = L.control.locate({
  		icon: 'fa fa-map-marker',
      locateOptions: {enableHighAccuracy: true},
      strings: {
          title: "Lokasi Saya",
  				popup: "Anda berada di sekitar {distance} {unit} dari titik ini"
      }

  	}).addTo(peta_area);

  	peta_area.on('locationfound', function(e) {
  	    peta_area.setView(e.latlng)
  	});

    peta_area.on('startfollowing', function() {
      peta_area.on('dragstart', lc._stopFollowing, lc);
  	}).on('stopfollowing', function() {
      peta_area.off('dragstart', lc._stopFollowing, lc);
  	});

    //Menghapus Peta wilayah
    peta_area.on('pm:globalremovalmodetoggled', function(e)
    {
      document.getElementById('path').value = '';
    })

    L.control.layers(baseLayers, overlayLayers, {position: 'topleft', collapsed: true}).addTo(peta_area);

  }; //EOF window.onload
</script>
<script src="<?= base_url()?>assets/js/leaflet.filelayer.js"></script>
<script src="<?= base_url()?>assets/js/togeojson.js"></script>
