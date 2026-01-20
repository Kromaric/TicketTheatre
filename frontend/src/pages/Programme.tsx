import { useMemo, useState } from "react";
import { Box, Flex, Grid, Text, Button, IconButton, Portal } from "@chakra-ui/react";
import { SeancesNav } from "../components/SeancesNav";
import { TicketLabel } from "../components/TicketLabel";
import { TicketCard } from "../components/TicketCard";
import { Filter } from "../components/Filter";
import { LuX } from "react-icons/lu";

type SeancesTab = "affiche" | "toutes";

type FilterValues = {
  genre: string;
  metteurEnScene: string;
  date: string;
};

function formatShortDay(d: Date) {
  return d
    .toLocaleDateString("fr-FR", { weekday: "short", day: "2-digit" })
    .toUpperCase();
}

function dayLabelByIndex(d: Date, idx: number) {
  if (idx === 0) return "AUJOURD’HUI";
  if (idx === 1) return "DEMAIN";
  if (idx === 2) return "APRÈS-DEMAIN";
  return formatShortDay(d);
}

export default function Programme() {
  const [tab, setTab] = useState<SeancesTab>("affiche");

  const days = useMemo(() => {
    const base = new Date();
    base.setHours(0, 0, 0, 0);
    return Array.from({ length: 7 }, (_, i) => {
      const d = new Date(base);
      d.setDate(base.getDate() + i);
      return d;
    });
  }, []);

  const [selectedDayIndex, setSelectedDayIndex] = useState(0);

  const afficheByDayCount = useMemo(() => {
    return [6, 3, 9, 6, 6, 12, 4];
  }, []);

  const visibleAfficheCount = afficheByDayCount[selectedDayIndex] ?? 6;

  const allSeancesCount = 18;

  const [filters, setFilters] = useState<FilterValues>({
    genre: "",
    metteurEnScene: "",
    date: "",
  });

  const [isFilterOpen, setIsFilterOpen] = useState(false);

  return (
    <Box w="full" maxW="1200px" mx="auto" pt={4} pb={8}>
      <Flex justify="center" mb={6}>
        <SeancesNav value={tab} onChange={setTab} />
      </Flex>

      {tab === "affiche" && (
        <>
          <Flex
            gap={3}
            pb={2}
            mb={6}
            align="center"
            justify="center"                         
            flexWrap="wrap"                      
            w="full"
            >
            {days.map((d, idx) => {
              const label = dayLabelByIndex(d, idx);
              const isActive = idx === selectedDayIndex;

              return (
                <Box key={d.toISOString()} opacity={isActive ? 1 : 0.85}>
                  <TicketLabel
                    text={label}
                    variant={isActive ? "red" : "yellow"}
                    onClick={() => setSelectedDayIndex(idx)}
                  />
                </Box>
              );
            })}
          </Flex>

          <Text mb={4} fontWeight="semibold">
            Séances du{" "}
            {days[selectedDayIndex].toLocaleDateString("fr-FR", {
              weekday: "long",
              day: "2-digit",
              month: "long",
            })}
          </Text>

          <Grid
            templateColumns={{ base: "1fr", md: "repeat(3, 1fr)" }}
            gap={6}
            justifyItems="center"
          >
            {Array.from({ length: visibleAfficheCount }).map((_, i) => (
              <TicketCard key={`affiche-${selectedDayIndex}-${i}`} />
            ))}
          </Grid>
        </>
      )}

      {tab === "toutes" && (
        <>
          <Flex justify="center" mb={6}>
            <Button
              bg="red.800"
              color="white"
              borderRadius="full"
              px="8"
              _hover={{ bg: "red.700" }}
              onClick={() => setIsFilterOpen(true)}
            >
              FILTRER PAR
            </Button>
          </Flex>

          <Text mb={4} fontWeight="semibold">
            Toutes les séances
            {(filters.genre || filters.metteurEnScene || filters.date) && (
              <> — filtres appliqués</>
            )}
          </Text>

          <Grid
            templateColumns={{ base: "1fr", md: "repeat(3, 1fr)" }}
            gap={6}
            justifyItems="center"
          >
            {Array.from({ length: allSeancesCount }).map((_, i) => (
              <TicketCard key={`toutes-${i}`} />
            ))}
          </Grid>

          {/* POPUP FILTER */}
          {isFilterOpen && (
            <Portal>
              <Box
                position="fixed"
                inset="0"
                bg="rgba(0,0,0,0.6)"
                zIndex="3000"
                display="flex"
                alignItems="center"
                justifyContent="center"
                p={4}
                onClick={() => setIsFilterOpen(false)}
              >
                <Box
                  position="relative"
                  onClick={(e) => e.stopPropagation()}
                >
                  <IconButton
                    aria-label="Fermer"
                    position="absolute"
                    top="-12px"
                    right="-12px"
                    borderRadius="full"
                    bg="black"
                    color="white"
                    _hover={{ bg: "gray.800" }}
                    onClick={() => setIsFilterOpen(false)}
                  >
                    <LuX />
                  </IconButton>

                  <Filter
                    onValidate={(values) => {
                      setFilters(values);
                      console.log("Filters:", values);
                      setIsFilterOpen(false);
                    }}
                  />
                </Box>
              </Box>
            </Portal>
          )}
        </>
      )}
    </Box>
  );
}
