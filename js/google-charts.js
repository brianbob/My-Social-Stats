(function ($, Drupal, drupalSettings) {
  $(document).ready(function() {
    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});
    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(drawFacebookPostsChart);

  });

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and
  // draws it.
  function drawFacebookPostsChart() {

    // Create the data table.
    var data_object = drupalSettings.facebook_block.facebook_posts;

    //console.log(Object.values(data_array));
    var data_array = [Object.entries(data_object)];
    console.log(data_array);
    var data = google.visualization.arrayToDataTable(data_array);

    // Set chart options
    var options = {
      'title': 'Facebook Posts',
      'width':400,
      'height':300,
      'seriesType': 'bars',
      //'series': {5: {type: 'line'}}
    };

    // Instantiate and draw our chart, passing in some options.
    var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
    chart.draw(data, options);
  }
}(jQuery, Drupal, drupalSettings));
