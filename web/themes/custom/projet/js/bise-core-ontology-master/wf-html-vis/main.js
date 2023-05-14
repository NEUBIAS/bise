/*
* @Author: Alban Gaignard
* @Date:   2020-03-01 15:55:48
* @Last Modified by:   Alban Gaignard
* @Last Modified time: 2020-03-02 13:04:39
*/
(function ($) {
// var node_id = "1432"
  //var node_id = "50";
  //var node_url = "http://test.biii.eu/node/" + node_id;
  //var node_url = "http://biii.eu/node/" + node_id;
//var node_url_json = node_url + "?_format=json "

  var nodes = new Set();
  var edges = [];

  var cyjs_nodes = [];
  var cyjs_edges = [];

  var get_node_id = function () {
    var url = $("[rel='shortlink']").attr('href');
    var id = url.substring(url.lastIndexOf("/") + 1, url.length);
    return id;
  };
//
//
//
  var node2cy = function (x) {
    // console.log({data: {id: x} })
    return {data: {id: x, name: x}}
  };

//
//
//
  var edge2cy = function (x) {
    // console.log({data: {source: x.source, target: x.target} })
    return {data: {source: x.source, target: x.target}}
  };

  var updateWorkflowVis = function () {

    // $.getJSON('http://test.biii.eu/wfsteps?_format=json', function(data) {
    $.getJSON('//biii.eu/wfsteps?_format=json', function (data) {

      // $.getJSON(json_node_url + "?_format=json ", function(data) {
      //console.log(data);

      $.each(data, function (key, value) {

        //console.log( key + ": " + value["parent_id"] );
        if (value["parent_id"] == get_node_id()) {

          nodes.add(value["field_current_workflow_step__1"]);
          if (value["field_previous_workflow_step"].includes(", ")) {

            $.each(value["field_previous_workflow_step"].split(", "), function (key, sub_value) {
              nodes.add(sub_value);
              edges.push({
                source: sub_value,
                target: value["field_current_workflow_step__1"]
              })
            })
          }
          else {

            nodes.add(value["field_previous_workflow_step"]);
            edges.push({
              source: value["field_previous_workflow_step"],
              target: value["field_current_workflow_step__1"]
            })
          }

          //console.log( value["field_current_workflow_step__1"] );
          //console.log( value["field_previous_workflow_step"] );
        }
      });

      //console.log(nodes);
      //console.log(edges);

      cyjs_nodes = [...nodes].filter(x => x != "").map(node2cy);
      cyjs_edges = [...edges].filter(x => x.source != "" && x.target != "").map(edge2cy);

      var cy = window.cy = cytoscape({
        container: document.getElementById('WF_vis'),

        boxSelectionEnabled: false,
        autounselectify: true,

        layout: {
          name: 'dagre'
        },

        style: [
          {
            selector: 'node',
            style: {
              'border-color': '#11479e',
              'border-width': '2px',
              'background-color': 'white',
              'content': 'data(name)',
              'shape': 'roundrectangle',
              'text-valign': 'center',
              'text-halign': 'center',
              'text-wrap': 'wrap',
              'text-max-width': '130px',
              'width': '150px',
              'height': 'label',
              'padding-left': '5px',
              'padding-right': '5px',
              'padding-top': '10px',
              'padding-bottom': '10px',
            }
          },

          {
            selector: 'edge',
            style: {
              'width': 4,
              'target-arrow-shape': 'triangle',
              'line-color': '#9dbaea',
              'target-arrow-color': '#9dbaea',
              'curve-style': 'bezier'
            }
          }
        ],

        elements: {
          nodes: cyjs_nodes,
          edges: cyjs_edges
        }
      });

      // cy.nodeHtmlLabel([ {
      //  			query: 'node', // cytoscape query selector
      //  			halign: 'center', // title vertical position. Can be
      // 'left',''center, 'right' valign: 'center', // title vertical position.
      // Can be 'top',''center, 'bottom' halignBox: 'center', // title vertical
      // position. Can be 'left',''center, 'right' valignBox: 'center', //
      // title relative box vertical position. Can be 'top',''center, 'bottom'
      // cssClass: '', // any classes will be as attribute of <div> container
      // for every title tpl(data) { return '<a href="node_url">' + data.name +
      // '</a>'; // your html template here } } ]);

    });
  };

  updateWorkflowVis();
})(jQuery);
// console.log('NODES')
// console.log(cyjs_nodes)
// console.log('----')
// console.log('EDGES')
// console.log(cyjs_edges)
// console.log('----')

