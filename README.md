# school_manager API VERSION 1
## Routes Api :

### Ecoles
| Path                   | Verbe  |
|:-----------------------|-------:|
|/api/v1/ecoles GET      | GET    |
|/api/v1/ecoles POST     | POST   |
|/api/v1/ecoles/([0-9]+) | GET    |
|/api/v1/ecoles/([0-9]+) | PUT    | 
|/api/v1/ecoles/([0-9]+) | PATCH  | 
|/api/v1/ecoles/([0-9]+) | DELETE |

### Page
| Path                       | Verbe  |
|:---------------------------|-------:|
|api/v1/ecoles/page/([0-9]+) | GET    |

### Adresses
| Path                                      | Verbe  |
|:------------------------------------------|-------:|
|/api/v1/ecoles/([0-9]+)/adresses           | GET    |
|/api/v1/ecoles/([0-9]+)/adresses           | POST   |
|/api/v1/ecoles/([0-9]+)/adresses/([0-9]+)  | GET    |
|/api/v1/ecoles/([0-9]+)/adresses/([0-9]+)  | PUT    |
|/api/v1/ecoles/([0-9]+)/adresses/([0-9]+)  | PATCH  |
|/api/v1/ecoles/([0-9]+)/adresses/([0-9]+)  | DELETE |

### Images
| Path                                      | Verbe  |
|:------------------------------------------|-------:|
|/api/v1/ecoles/([0-9]+)/images             | GET    |
|/api/v1/ecoles/([0-9]+)/images             | POST   |
|/api/v1/ecoles/([0-9]+)/images/([0-9]+)    | GET    |
|/api/v1/ecoles/([0-9]+)/images/([0-9]+)    | PUT    |
|/api/v1/ecoles/([0-9]+)/images/([0-9]+)    | PATCH  |
|/api/v1/ecoles/([0-9]+)/images/([0-9]+)    | DELETE |