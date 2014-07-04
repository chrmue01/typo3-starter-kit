class setup {
    exec { "apt-get update":
        path => "/usr/bin",
    }    
}
