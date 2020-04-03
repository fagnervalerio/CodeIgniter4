<?php 

function alert_conceito($conceito)
{    
    switch(strtoupper($conceito)) {
        case "ÓTIMO":
            return "alert alert-success";
            break;
        case "MUITO BOM":
            return "alert alert-primary";
            break;
        case "BOM":
            return "alert alert-info";
            break;
        case "REGULAR":
            return "alert alert-warning";
            break;
        case "FRACO":
            return "alert alert-danger";
            break;
        default:
            return "alert alert-secondary";
            break;
    }
}