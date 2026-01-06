import { Card, Center, Text } from "@chakra-ui/react";

export const VelumCard = () => {
  return (
    <Card.Root
      maxW="260px"
      bg="yellow.500"
      color="black"
      borderRadius="md"
      p="3"
      pt="0.5"
    >
      <Card.Header pb="0">
        <Card.Title
          textAlign="center"
          textTransform="uppercase"
          fontSize="sm"
        >
          LE VELUM
        </Card.Title>
      </Card.Header>

      <Card.Body pt="1" pb="5" textAlign="Center" fontWeight="medium">
        <Text fontSize="0.6rem">
          Sous son nom emprunté au grand voile des théâtres romains, Le Vélum
          est un lieu à taille humaine où la scène s’ouvre comme une agora
          moderne. Entre colonnes suggérées et lignes épurées, la salle évoque
          la cavea antique : on s’y installe en demi-cercle, proche des
          artistes, pour entendre chaque souffle, chaque rumeur, comme dans
          l’orchestra d’autrefois.
        </Text>

        <Text fontSize="0.6rem" mt="2">
          Ici, les classiques croisent les écritures d’aujourd’hui : tragédie
          ou farce, slam ou comédie musicale, concert de chambre ou stand-up,
          tout trouve sa place sous notre “voile” acoustique. Avant et après
          spectacle, l’atrium vibre : on débriefe au comptoir, on feuillette
          l’affiche du mois, on se promet de revenir.
        </Text>

        <Text fontSize="0.6rem" mt="2">
          Fondé à l’an –27 (ou presque), Le Vélum célèbre l’art de rassembler.
          Venez voir comment l’esprit romain — le goût du beau, du jeu et du
          débat — se réinvente chaque soir, ici et maintenant.
        </Text>
      </Card.Body>
    </Card.Root>
  );
};
