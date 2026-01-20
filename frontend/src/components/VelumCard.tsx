import { Card, Text } from "@chakra-ui/react";

export const VelumCard = () => {
  return (
    <Card.Root
      w="full"
      maxW="720px"
      minH={{ base: "70vh", md: "560px" }}   
      bg="yellow.500"
      color="black"
      borderRadius="lg"
      p="8"                         
    >
      <Card.Header pb="4">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          fontSize="lg"
          letterSpacing="wide"
        >
          LE VELUM
        </Card.Title>
      </Card.Header>

      <Card.Body
        pt="0"
        pb="2"
        textAlign="center"
        fontWeight="medium"
        display="flex"
        flexDirection="column"
        justifyContent="center"          
      >
        <Text fontSize={{ base: "sm", md: "md" }} lineHeight="tall">
          Sous son nom emprunté au grand voile des théâtres romains, Le Vélum
          est un lieu à taille humaine où la scène s’ouvre comme une agora
          moderne. Entre colonnes suggérées et lignes épurées, la salle évoque
          la cavea antique : on s’y installe en demi-cercle, proche des
          artistes, pour entendre chaque souffle, chaque rumeur, comme dans
          l’orchestra d’autrefois.
        </Text>

        <Text fontSize={{ base: "sm", md: "md" }} lineHeight="tall" mt="6">
          Ici, les classiques croisent les écritures d’aujourd’hui : tragédie
          ou farce, slam ou comédie musicale, concert de chambre ou stand-up,
          tout trouve sa place sous notre “voile” acoustique. Avant et après
          spectacle, l’atrium vibre : on débriefe au comptoir, on feuillette
          l’affiche du mois, on se promet de revenir.
        </Text>

        <Text fontSize={{ base: "sm", md: "md" }} lineHeight="tall" mt="6">
          Fondé à l’an –27 (ou presque), Le Vélum célèbre l’art de rassembler.
          Venez voir comment l’esprit romain — le goût du beau, du jeu et du
          débat — se réinvente chaque soir, ici et maintenant.
        </Text>
      </Card.Body>
    </Card.Root>
  );
};
