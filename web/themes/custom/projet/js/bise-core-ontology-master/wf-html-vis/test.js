/*
* @Author: Alban Gaignard
* @Date:   2020-03-01 15:55:48
* @Last Modified by:   Alban Gaignard
* @Last Modified time: 2020-03-02 11:59:09
*/

var node_id = "1432"
var node_url = "http://test.biii.eu/node/" + node_id
//var node_url_json = node_url + "?_format=json "

var nodes = new Set();
var edges = [];

var cyjs_nodes = [];
var cyjs_edges = [];

//
//
//
var node2cy = function (x) {
  // console.log({data: {id: x} })
  return {data: {id: x, name: x}}
}

//
//
//
var edge2cy = function (x) {
  // console.log({data: {source: x.source, target: x.target} })
  return {data: {source: x.source, target: x.target}}
}

var updateWorkflowVis = function (json_node_url) {

  $.getJSON('http://test.biii.eu/wfsteps?_format=json', function (data) {
    //$.getJSON(json_node_url + "?_format=json", function(data) {
    console.log(data);

    $.each(data, function (key, value) {
      // console.log( key + ": " + value["parent_id"] );
      if (value["parent_id"] == node_id) {
        nodes.add(value["field_current_workflow_step__1"])
        nodes.add(value["field_previous_workflow_step"])
        edges.push({
          source: value["field_previous_workflow_step"],
          target: value["field_current_workflow_step__1"]
        })
        //console.log( value["field_current_workflow_step__1"] );
        //console.log( value["field_previous_workflow_step"] );
      }
    });

    console.log(nodes)
    console.log(edges)

    cyjs_nodes = [...nodes].filter(x => x != "").map(node2cy)
    cyjs_edges = [...edges].filter(x => x.source != "" && x.target != "").map(edge2cy)

    var cy = window.cy = cytoscape({
      container: document.getElementById('cy'),

      boxSelectionEnabled: false,
      autounselectify: true,

      layout: {
        name: 'dagre'
      },

      style: [
        {
          selector: 'node',
          style: {
            'background-color': '#11479e',
            'label': 'data(name)',
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

  });
}

updateWorkflowVis(node_url)

//console.log(cyjs_nodes)
//console.log(cyjs_edges)

